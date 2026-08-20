<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateCategory;
use App\Models\Nationality;
use App\Services\CandidateService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CandidateController extends Controller
{
    public function __construct(protected CandidateService $candidates) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'q', 'nationality', 'category', 'availability',
            'experience', 'age_min', 'age_max', 'language', 'sort',
        ]);

        $query = Candidate::query()
            ->with(['nationality:id,name_ar,slug,flag', 'category:id,name_ar,slug'])
            ->active();

        $candidates = $this->candidates
            ->filter($query, $filters)
            ->paginate(12)
            // Keeps every active filter on pagination links.
            ->withQueryString();

        return view('candidates.index', [
            'candidates' => $candidates,
            'nationalities' => Nationality::query()->active()->ordered()->get(),
            'categories' => CandidateCategory::query()->active()->ordered()->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Candidate $candidate): View
    {
        // Inactive profiles, and those with no uploaded CV, are not publicly
        // reachable — matching the active() scope used by the listings so a
        // direct URL cannot bypass it.
        if (! $candidate->is_active || blank($candidate->cv_file)) {
            throw new NotFoundHttpException();
        }

        $candidate->load(['nationality', 'category']);

        $related = Candidate::query()
            ->with(['nationality:id,name_ar,slug,flag', 'category:id,name_ar,slug'])
            ->active()
            ->available()
            ->where('id', '!=', $candidate->id)
            ->where('candidate_category_id', $candidate->candidate_category_id)
            ->limit(4)
            ->get();

        return view('candidates.show', [
            'candidate' => $candidate,
            'related' => $related,
            'whatsappMessage' => "السلام عليكم، أرغب بالاستفسار عن العاملة رقم: {$candidate->reference_number}",
        ]);
    }
}
