<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStoreCandidateRequest;
use App\Http\Requests\Admin\StoreCandidateRequest;
use App\Http\Requests\Admin\UpdateCandidateRequest;
use App\Models\Candidate;
use App\Models\CandidateCategory;
use App\Models\Nationality;
use App\Services\ActivityLogger;
use App\Services\CandidateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function __construct(
        protected CandidateService $candidates,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $candidates = Candidate::query()
            ->with(['nationality:id,name_ar', 'category:id,name_ar'])
            ->search($request->input('q'))
            ->when($request->filled('nationality'),
                fn ($q) => $q->where('nationality_id', $request->integer('nationality')))
            ->when($request->filled('category'),
                fn ($q) => $q->where('candidate_category_id', $request->integer('category')))
            ->when($request->filled('availability'),
                fn ($q) => $q->where('availability_status', $request->input('availability')))
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->input('status') === 'active');
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.candidates.index', [
            'candidates' => $candidates,
            'nationalities' => Nationality::query()->ordered()->get(),
            'categories' => CandidateCategory::query()->ordered()->get(),
            'filters' => $request->only(['q', 'nationality', 'category', 'availability', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.candidates.create', $this->formData());
    }

    public function store(StoreCandidateRequest $request): RedirectResponse
    {
        $candidate = $this->candidates->create(
            $request->safe()->except(['profile_image', 'cv_file', 'intro_video']),
            [
                'profile_image' => $request->file('profile_image'),
                'cv_file' => $request->file('cv_file'),
                'intro_video' => $request->file('intro_video'),
            ],
        );

        $this->activity->log('candidate.created', $candidate, ['reference' => $candidate->reference_number]);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', "تم إنشاء السيرة الذاتية بنجاح برقم {$candidate->reference_number}.");
    }

    /** Bulk import: many CVs, one nationality/category for the whole batch. */
    public function bulkCreate(): View
    {
        return view('admin.candidates.bulk', [
            ...$this->formData(),
            'maxFiles' => BulkStoreCandidateRequest::MAX_FILES,
        ]);
    }

    public function bulkStore(BulkStoreCandidateRequest $request): RedirectResponse
    {
        $result = $this->candidates->createFromCvFiles(
            $request->file('cv_files', []),
            $request->safe()->except('cv_files'),
        );

        $createdCount = count($result['created']);
        $failedCount = count($result['failed']);

        foreach ($result['created'] as $candidate) {
            $this->activity->log('candidate.created', $candidate, [
                'reference' => $candidate->reference_number,
                'source' => 'bulk_upload',
            ]);
        }

        if ($createdCount === 0) {
            return back()
                ->withInput()
                ->with('error', 'لم يتم إنشاء أي سيرة ذاتية. يرجى مراجعة الملفات والمحاولة مرة أخرى.');
        }

        $message = "تم إنشاء {$createdCount} سيرة ذاتية بنجاح.";

        if ($failedCount > 0) {
            $names = implode('، ', array_column($result['failed'], 'file'));
            $message .= " تعذّر رفع {$failedCount} ملف: {$names}";
        }

        return redirect()
            ->route('admin.candidates.index')
            ->with($failedCount > 0 ? 'warning' : 'success', $message);
    }

    public function show(Candidate $candidate): View
    {
        $candidate->load(['nationality', 'category', 'requests' => fn ($q) => $q->latest()->limit(20)]);

        return view('admin.candidates.show', ['candidate' => $candidate]);
    }

    public function edit(Candidate $candidate): View
    {
        return view('admin.candidates.edit', [
            'candidate' => $candidate,
            ...$this->formData(),
        ]);
    }

    public function update(UpdateCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->candidates->update(
            $candidate,
            $request->safe()->except(['profile_image', 'cv_file', 'intro_video']),
            [
                'profile_image' => $request->file('profile_image'),
                'cv_file' => $request->file('cv_file'),
                'intro_video' => $request->file('intro_video'),
            ],
        );

        $this->activity->log('candidate.updated', $candidate, ['reference' => $candidate->reference_number]);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', 'تم تحديث بيانات السيرة الذاتية بنجاح.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        // Soft delete: files are retained until the record is purged.
        $candidate->delete();

        $this->activity->log('candidate.deleted', $candidate, ['reference' => $candidate->reference_number]);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', 'تم حذف السيرة الذاتية.');
    }

    public function updateAvailability(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $request->validate([
            'availability_status' => ['required', Rule::in(AvailabilityStatus::values())],
        ]);

        $candidate->update($validated);

        $this->activity->log('candidate.availability_changed', $candidate, [
            'status' => $validated['availability_status'],
        ]);

        return back()->with('success', 'تم تحديث حالة التوفر.');
    }

    public function toggleFeatured(Candidate $candidate): RedirectResponse
    {
        $candidate->update(['featured' => ! $candidate->featured]);

        return back()->with('success', $candidate->featured ? 'تمت الإضافة للمميزة.' : 'تمت الإزالة من المميزة.');
    }

    public function toggleActive(Candidate $candidate): RedirectResponse
    {
        $candidate->update(['is_active' => ! $candidate->is_active]);

        return back()->with('success', $candidate->is_active ? 'تم تفعيل السيرة.' : 'تم تعطيل السيرة.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'nationalities' => Nationality::query()->active()->ordered()->get(),
            'categories' => CandidateCategory::query()->active()->ordered()->get(),
            'availabilityOptions' => AvailabilityStatus::options(),
        ];
    }
}
