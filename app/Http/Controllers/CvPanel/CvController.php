<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use App\Services\CvPanel\CvPurgeService;
use App\Services\CvPanel\CvReservationService;
use App\Services\CvPanel\CvUploadService;
use App\Services\CvPanel\WorkerActivityLogger;
use App\Support\CvPanel\CvPanelPermissions;
use App\Support\CvPanel\CvStreamer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CvController extends Controller
{
    public function __construct(
        private readonly CvUploadService $uploads,
        private readonly CvPurgeService $purge,
        private readonly CvReservationService $reservations,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $scope = CvPanelPermissions::visibleNationalityIds($user);

        $workers = Worker::query()
            ->with(['nationality', 'client', 'assignedBy', 'latestContract'])
            ->whereNotNull('cv_path')
            ->where('active', true)
            // assigned is the end of the cycle and is followed up elsewhere.
            ->where('status', '!=', Worker::STATUS_ASSIGNED)
            ->forNationalities($scope)
            ->when($request->filled('nationality_id'),
                fn ($q) => $q->where('nationality_id', $request->integer('nationality_id')))
            ->when($request->filled('status') && $request->input('status') !== Worker::STATUS_ASSIGNED,
                fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('experience'),
                fn ($q) => $q->where('experience', $request->string('experience')))
            ->when($request->filled('religion'),
                fn ($q) => $q->where('religion', $request->string('religion')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->input('search'));

                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', '%'.$term.'%');

                    // A numeric term also matches the row id exactly.
                    if (ctype_digit($term)) {
                        $inner->orWhere('id', (int) $term);
                    }
                });
            })
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('cv-panel.cvs.index', [
            'workers' => $workers,
            'nationalities' => $this->scopedNationalities($scope),
        ]);
    }

    /** Streams the PDF inline; never exposes the stored file name. */
    public function file(Request $request, int $id): Response
    {
        $worker = $this->findInScope($request, $id);

        abort_unless($worker->hasCvFile(), 404);

        return CvStreamer::inline($worker, $request, 'private, max-age=600');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $worker = $this->findInScope($request, $id);

        if (! CvPanelPermissions::canDeleteWorker($request->user(), $worker)) {
            return back()->with('error', __('cv-panel.delete.not_allowed'));
        }

        // Soft delete: the row and the file are kept.
        $worker->delete();

        WorkerActivityLogger::log(
            $worker,
            WorkerActivityLog::ACTION_DELETED,
            'حُذفت السيرة الذاتية من لوحة السير الذاتية (حذف ناعم)',
            $request->user(),
        );

        return back()->with('success', 'تم حذف السيرة الذاتية.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        $user = $request->user();
        $scope = CvPanelPermissions::visibleNationalityIds($user);

        $workers = Worker::query()
            ->whereIn('id', $request->input('ids'))
            ->forNationalities($scope)
            ->with('latestContract')
            ->get();

        $deleted = 0;
        $skipped = 0;

        foreach ($workers as $worker) {
            // Booked rows are skipped rather than refused, so one bad pick
            // does not fail the whole batch.
            if (! CvPanelPermissions::canDeleteWorker($user, $worker)) {
                $skipped++;

                continue;
            }

            $worker->delete();
            $deleted++;

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_DELETED,
                'حُذفت ضمن حذف جماعي من لوحة السير الذاتية (حذف ناعم)',
                $user,
            );
        }

        // Anything outside the user's scope never loaded, so count it too.
        $skipped += count($request->input('ids')) - $workers->count();

        return back()->with('success', __('cv-panel.delete.bulk_result', [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]));
    }

    /** Coordinator follow-up: reserved CVs and the ones already assigned. */
    public function reserved(Request $request): View
    {
        abort_unless(CvPanelPermissions::canFollowUpReserved($request->user()), 403);

        $scope = CvPanelPermissions::visibleNationalityIds($request->user());
        $tab = $request->input('tab') === Worker::STATUS_ASSIGNED
            ? Worker::STATUS_ASSIGNED
            : Worker::STATUS_RESERVED;

        $base = fn (string $status) => Worker::query()
            ->where('status', $status)
            ->forNationalities($scope);

        $workers = $base($tab)
            ->with(['nationality', 'client', 'assignedBy'])
            ->when($request->filled('nationality_id'),
                fn ($q) => $q->where('nationality_id', $request->integer('nationality_id')))
            ->orderByDesc('assigned_at')
            ->paginate(24)
            ->withQueryString();

        return view('cv-panel.cvs.reserved', [
            'workers' => $workers,
            'tab' => $tab,
            // Tab counts ignore the nationality filter.
            'reservedCount' => $base(Worker::STATUS_RESERVED)->count(),
            'assignedCount' => $base(Worker::STATUS_ASSIGNED)->count(),
            'nationalities' => $this->scopedNationalities($scope),
        ]);
    }

    public function markAssigned(Request $request, int $id): RedirectResponse
    {
        abort_unless(CvPanelPermissions::canFollowUpReserved($request->user()), 403);

        $worker = $this->findInScope($request, $id);

        try {
            $this->reservations->markAssigned($worker, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('cv-panel.assigned.success'));
    }

    public function uploadForm(Request $request): View
    {
        abort_unless(CvPanelPermissions::canUpload($request->user()), 403);

        $scope = CvPanelPermissions::visibleNationalityIds($request->user());
        $nationalityId = $request->integer('nationality_id') ?: null;

        return view('cv-panel.upload', [
            'nationalities' => $this->scopedNationalities($scope),
            'selectedNationalityId' => $nationalityId,
            'purgeCount' => $this->purge->countFor($nationalityId),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(CvPanelPermissions::canUpload($user), 403);

        $scope = CvPanelPermissions::visibleNationalityIds($user);
        $allowed = $scope ?? Nationality::query()->pluck('id')->all();

        $data = $request->validate([
            // Re-validated against the user's own nationalities, not just any
            // existing one.
            'nationality_id' => ['required', 'integer', Rule::in($allowed)],
            'experience' => ['required', Rule::in(array_keys(__('workers.experience')))],
            'religion' => ['required', Rule::in(array_keys(__('workers.religion')))],
            'profession' => ['nullable', 'string', 'max:255'],
            'cvs' => ['required', 'array', 'min:1', 'max:100'],
            'cvs.*' => ['file', 'mimes:pdf', 'max:10240'],
            'purge_old' => ['boolean'],
        ]);

        $result = $this->uploads->handle(
            files: $request->file('cvs'),
            nationalityId: (int) $data['nationality_id'],
            experience: $data['experience'],
            religion: $data['religion'],
            profession: $data['profession'] ?? null,
            actor: $user,
            purgeOld: $request->boolean('purge_old'),
        );

        $message = __('cv-panel.upload.success', ['count' => $result['created']]);

        if ($result['duplicates'] !== []) {
            $message .= ' '.__('cv-panel.upload.duplicates', [
                'count' => count($result['duplicates']),
                'names' => implode('، ', array_slice($result['duplicates'], 0, 5)),
            ]);
        }

        if ($result['purged'] > 0) {
            $message .= ' '.__('cv-panel.upload.purged', ['count' => $result['purged']]);
        }

        return redirect()
            ->route('cv-panel.cvs.index', ['nationality_id' => $data['nationality_id']])
            ->with('success', $message);
    }

    /** Loads a worker, 404ing when it falls outside the user's nationalities. */
    private function findInScope(Request $request, int $id): Worker
    {
        $worker = Worker::with('latestContract')->findOrFail($id);

        $scope = CvPanelPermissions::visibleNationalityIds($request->user());

        abort_if($scope !== null && ! in_array((int) $worker->nationality_id, $scope, true), 404);

        return $worker;
    }

    private function scopedNationalities(?array $scope)
    {
        return Nationality::query()
            ->when($scope !== null, fn ($q) => $q->whereIn('id', $scope))
            ->ordered()
            ->get();
    }
}
