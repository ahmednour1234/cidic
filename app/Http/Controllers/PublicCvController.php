<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use App\Models\Worker;
use App\Support\CvPanel\CvStreamer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public CV catalogue.
 *
 * Only CVs that are active, available, have a file and were never withdrawn
 * are ever exposed. Passport and phone numbers are never rendered.
 */
class PublicCvController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request, ?Nationality $nationality = null): View|Response
    {
        $workers = $this->baseQuery()
            ->when($nationality, fn ($q) => $q->where('nationality_id', $nationality->id))
            ->when($request->filled('experience'),
                fn ($q) => $q->where('experience', $request->string('experience')))
            ->when($request->filled('religion'),
                fn ($q) => $q->where('religion', $request->string('religion')))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Infinite scroll fetches just the cards; the full page keeps a
        // <noscript> paginator as the fallback.
        if ($request->boolean('partial')) {
            return response()->view('cvs.partials.cards', ['workers' => $workers]);
        }

        return view('cvs.index', [
            'workers' => $workers,
            'nationality' => $nationality,
            'nationalities' => $this->nationalitiesWithCvs(),
        ]);
    }

    public function show(int $id): View
    {
        $worker = $this->baseQuery()->with('nationality')->findOrFail($id);

        $related = $this->baseQuery()
            ->where('nationality_id', $worker->nationality_id)
            ->whereKeyNot($worker->id)
            ->latest('id')
            ->limit(4)
            ->get();

        return view('cvs.show', [
            'worker' => $worker,
            'related' => $related,
        ]);
    }

    /** Streams the CV inline. Reserved CVs get an explanation, not a bare 404. */
    public function pdf(Request $request, int $id): Response
    {
        $worker = $this->baseQuery()->find($id);

        if ($worker === null) {
            // Distinguish "taken" from "never existed" so the visitor
            // understands why the link stopped working.
            $exists = Worker::query()->whereKey($id)->exists();

            abort_unless($exists, 404);

            return response()->view('cvs.reserved', [], 410);
        }

        abort_unless($worker->hasCvFile(), 404);

        return CvStreamer::inline($worker, $request, 'public, max-age=3600');
    }

    private function baseQuery(): Builder
    {
        return Worker::query()->publiclyVisible();
    }

    /** Only nationalities that actually have something to show. */
    private function nationalitiesWithCvs()
    {
        return Nationality::query()
            ->whereHas('workers', fn ($q) => $q->publiclyVisible())
            ->withCount(['workers as available_count' => fn ($q) => $q->publiclyVisible()])
            ->ordered()
            ->get();
    }
}
