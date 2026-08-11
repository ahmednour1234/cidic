<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Models\Candidate;
use App\Models\CandidateCategory;
use App\Support\ArabicSlug;
use App\Support\CvFilename;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CandidateService
{
    public function __construct(
        protected FileUploadService $files,
        protected ReferenceNumberService $references,
    ) {}

    /**
     * Create a candidate, deriving the reference number and slug from the
     * persisted id so concurrent writers cannot collide.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $uploads
     */
    public function create(array $data, array $uploads = []): Candidate
    {
        return DB::transaction(function () use ($data, $uploads) {
            $data = $this->normalise($data);

            // Temporary unique value satisfies the NOT NULL + UNIQUE constraints
            // until the real id-derived reference is known.
            $data['reference_number'] = 'TMP-' . bin2hex(random_bytes(8));
            $data['slug'] = 'tmp-' . bin2hex(random_bytes(8));

            $candidate = Candidate::create($data);

            $reference = $this->references->candidateReference($candidate->id);

            $candidate->forceFill([
                'reference_number' => $reference,
                'slug' => $this->uniqueSlug(ArabicSlug::forCandidate($reference, $candidate->name), $candidate->id),
            ])->save();

            $this->syncFiles($candidate, $uploads);

            return $candidate->refresh();
        });
    }

    /**
     * Create one candidate per uploaded CV.
     *
     * Only the nationality and category are supplied by the admin; the name is
     * derived from each filename. Every CV is imported in its own transaction so
     * a single bad file cannot roll back the whole batch.
     *
     * @param  list<UploadedFile>  $files
     * @param  array<string, mixed>  $shared  nationality_id, candidate_category_id, and optional defaults
     * @return array{created: list<Candidate>, failed: list<array{file: string, error: string}>}
     */
    public function createFromCvFiles(array $files, array $shared): array
    {
        $created = [];
        $failed = [];

        foreach ($files as $file) {
            $original = $file->getClientOriginalName();

            try {
                $created[] = $this->createFromCvFile($file, $shared);
            } catch (Throwable $e) {
                report($e);

                $failed[] = [
                    'file' => $original,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ['created' => $created, 'failed' => $failed];
    }

    /**
     * @param  array<string, mixed>  $shared
     */
    protected function createFromCvFile(UploadedFile $file, array $shared): Candidate
    {
        $name = CvFilename::toName($file->getClientOriginalName());

        // Profession is required by the schema but unknown from a PDF, so it
        // defaults to the chosen category's Arabic name.
        $profession = $shared['profession']
            ?? CandidateCategory::find($shared['candidate_category_id'])?->name_ar
            ?? $name;

        $data = [
            'name' => $name,
            'nationality_id' => $shared['nationality_id'],
            'candidate_category_id' => $shared['candidate_category_id'],
            'profession' => $profession,
            'years_of_experience' => $shared['years_of_experience'] ?? 0,
            'gender' => $shared['gender'] ?? 'female',
            'language_arabic' => $shared['language_arabic'] ?? 'none',
            'language_english' => $shared['language_english'] ?? 'none',
            'availability_status' => $shared['availability_status'] ?? AvailabilityStatus::Available->value,
            'is_active' => $shared['is_active'] ?? true,
            'featured' => false,
        ];

        return $this->create($data, ['cv_file' => $file]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $uploads
     */
    public function update(Candidate $candidate, array $data, array $uploads = []): Candidate
    {
        return DB::transaction(function () use ($candidate, $data, $uploads) {
            $data = $this->normalise($data);

            // Keep the slug aligned with the name while preserving the reference prefix.
            if (isset($data['name']) && $data['name'] !== $candidate->name) {
                $data['slug'] = $this->uniqueSlug(
                    ArabicSlug::forCandidate($candidate->reference_number, $data['name']),
                    $candidate->id,
                );
            }

            $candidate->update($data);
            $this->syncFiles($candidate, $uploads);

            return $candidate->refresh();
        });
    }

    /**
     * Store uploaded files, replacing and cleaning up any previous version.
     *
     * @param  array<string, UploadedFile|null>  $uploads
     */
    protected function syncFiles(Candidate $candidate, array $uploads): void
    {
        $map = [
            'profile_image' => 'candidates/photos',
            'cv_file' => 'candidates/cvs',
            'intro_video' => 'candidates/videos',
        ];

        $changes = [];

        foreach ($map as $field => $directory) {
            $file = $uploads[$field] ?? null;

            if ($file instanceof UploadedFile) {
                $changes[$field] = $this->files->replace($file, $directory, $candidate->{$field});
            }
        }

        if ($changes) {
            $candidate->forceFill($changes)->save();
        }
    }

    /**
     * Remove a candidate's stored files. Called on permanent deletion only, so
     * soft-deleted records keep their media.
     */
    public function deleteFiles(Candidate $candidate): void
    {
        foreach (['profile_image', 'cv_file', 'intro_video'] as $field) {
            $this->files->delete($candidate->{$field});
        }
    }

    /**
     * Normalise incoming form values into storable shapes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalise(array $data): array
    {
        foreach (['skills', 'previous_countries'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if (is_string($value)) {
                // Accept comma-separated input using either the ASCII comma or
                // the Arabic comma (U+060C), which is what Arabic keyboards produce.
                $parts = preg_split('/[,،]/u', $value) ?: [];

                $value = array_values(array_filter(array_map('trim', $parts), 'strlen'));
            }

            $data[$field] = $value ?: null;
        }

        // Derive age from date of birth when the admin leaves it blank.
        if (blank($data['age'] ?? null) && filled($data['date_of_birth'] ?? null)) {
            $data['age'] = now()->diffInYears($data['date_of_birth']);
        }

        return $data;
    }

    /** Ensure slug uniqueness without relying on a retry loop at insert time. */
    protected function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $suffix = 1;

        while (
            Candidate::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }

    /**
     * Apply the public listing filters. Every filter arrives as a GET parameter
     * so links and pagination stay shareable.
     *
     * @param  array<string, mixed>  $filters
     */
    public function filter(Builder $query, array $filters): Builder
    {
        $query
            ->when(filled($filters['q'] ?? null), fn (Builder $q) => $q->search($filters['q']))
            ->when(filled($filters['nationality'] ?? null), function (Builder $q) use ($filters) {
                $value = $filters['nationality'];
                // Accept either an id or a slug, per the documented URL examples.
                $q->whereHas('nationality', fn (Builder $n) => is_numeric($value)
                    ? $n->where('id', (int) $value)
                    : $n->where('slug', $value));
            })
            ->when(filled($filters['category'] ?? null), function (Builder $q) use ($filters) {
                $value = $filters['category'];
                $q->whereHas('category', fn (Builder $c) => is_numeric($value)
                    ? $c->where('id', (int) $value)
                    : $c->where('slug', $value));
            })
            ->when(filled($filters['availability'] ?? null),
                fn (Builder $q) => $q->where('availability_status', $filters['availability']))
            ->when(filled($filters['experience'] ?? null),
                fn (Builder $q) => $q->where('years_of_experience', '>=', (int) $filters['experience']))
            ->when(filled($filters['age_min'] ?? null),
                fn (Builder $q) => $q->where('age', '>=', (int) $filters['age_min']))
            ->when(filled($filters['age_max'] ?? null),
                fn (Builder $q) => $q->where('age', '<=', (int) $filters['age_max']))
            ->when(filled($filters['language'] ?? null), function (Builder $q) use ($filters) {
                $column = $filters['language'] === 'english' ? 'language_english' : 'language_arabic';
                $q->where($column, '!=', 'none');
            });

        return $this->sort($query, $filters['sort'] ?? null);
    }

    protected function sort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'experience' => $query->orderByDesc('years_of_experience')->orderByDesc('id'),
            'age_asc' => $query->orderBy('age')->orderByDesc('id'),
            'age_desc' => $query->orderByDesc('age')->orderByDesc('id'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }
}
