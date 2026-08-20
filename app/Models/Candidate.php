<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'name',
        'slug',
        'nationality_id',
        'candidate_category_id',
        'date_of_birth',
        'age',
        'gender',
        'religion',
        'marital_status',
        'children_count',
        'education',
        'profession',
        'years_of_experience',
        'salary',
        'contract_price',
        'language_arabic',
        'language_english',
        'other_languages',
        'skills',
        'previous_countries',
        'description',
        'profile_image',
        'cv_file',
        'intro_video',
        'availability_status',
        'featured',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'skills' => 'array',
            'previous_countries' => 'array',
            'availability_status' => AvailabilityStatus::class,
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
            'contract_price' => 'decimal:2',
            'age' => 'integer',
            'years_of_experience' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CandidateCategory::class, 'candidate_category_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(CandidateRequest::class);
    }

    /* ---------------------------------------------------------------------
     | Scopes
     * ------------------------------------------------------------------ */

    /**
     * Publicly listable candidates.
     *
     * A CV is what the public pages actually present, so a record without an
     * uploaded PDF is treated as incomplete and never surfaces publicly no
     * matter how its is_active flag is set.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNotNull('cv_file')->where('cv_file', '!=', '');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability_status', AvailabilityStatus::Available->value);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    /** Free-text search across name and reference number. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('reference_number', 'like', "%{$term}%");
        });
    }

    /* ---------------------------------------------------------------------
     | Accessors / helpers
     * ------------------------------------------------------------------ */

    public function isAvailable(): bool
    {
        return $this->availability_status === AvailabilityStatus::Available
            && $this->is_active;
    }

    public function getProfileImageUrlAttribute(): string
    {
        return $this->profile_image
            ? Storage::url($this->profile_image)
            : asset('images/candidate-placeholder.svg');
    }

    public function getCvFileUrlAttribute(): ?string
    {
        return $this->cv_file ? Storage::url($this->cv_file) : null;
    }

    public function getIntroVideoUrlAttribute(): ?string
    {
        return $this->intro_video ? Storage::url($this->intro_video) : null;
    }

    /** Age falls back to date_of_birth when not explicitly stored. */
    public function getDisplayAgeAttribute(): ?int
    {
        if ($this->age) {
            return $this->age;
        }

        return $this->date_of_birth?->age;
    }

    /** Arabic summary of spoken languages, e.g. "العربية - الإنجليزية". */
    public function getLanguagesLabelAttribute(): string
    {
        $languages = [];

        if ($this->language_arabic && $this->language_arabic !== 'none') {
            $languages[] = 'العربية';
        }

        if ($this->language_english && $this->language_english !== 'none') {
            $languages[] = 'الإنجليزية';
        }

        if (filled($this->other_languages)) {
            $languages[] = $this->other_languages;
        }

        return $languages ? implode(' - ', $languages) : '—';
    }
}
