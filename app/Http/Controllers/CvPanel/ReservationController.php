<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Worker;
use App\Services\CvPanel\ContractService;
use App\Services\CvPanel\CvReservationService;
use App\Support\CvPanel\CvPanelPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ReservationController extends Controller
{
    public function __construct(private readonly CvReservationService $reservations) {}

    public function create(Request $request, int $id): View
    {
        abort_unless(CvPanelPermissions::canReserve($request->user()), 403);

        $worker = Worker::with('nationality')->findOrFail($id);

        return view('cv-panel.cvs.reserve', ['worker' => $worker]);
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_unless(CvPanelPermissions::canReserve($user), 403);

        $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:32'],
        ]);

        $worker = Worker::findOrFail($id);

        try {
            $client = $this->reservations->resolveClient(
                $request->integer('client_id') ?: null,
                $request->input('client_name'),
                $request->input('client_phone'),
                $user,
            );

            $this->reservations->reserve($worker, $client, $user);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cv-panel.cvs.index')
            ->with('success', __('cv-panel.reserve.success'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $worker = Worker::with('latestContract')->findOrFail($id);

        try {
            $this->reservations->cancel($worker, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('cv-panel.cancel.success'));
    }

    /** One click: create the contract and end the reservation cycle. */
    public function createContract(Request $request, int $id): RedirectResponse
    {
        $worker = Worker::with('latestContract')->findOrFail($id);

        try {
            $contract = app(ContractService::class)->createFromReservation($worker, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('cv-panel.contract.created', ['number' => $contract->number]));
    }

    public function tamara(Request $request, int $id): RedirectResponse
    {
        $worker = Worker::findOrFail($id);

        try {
            $this->reservations->recordTamara($worker, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('cv-panel.tamara.success'));
    }

    /** Remote source for the reserve page's client picker. */
    public function searchClients(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(CvPanelPermissions::canSearchClients($user), 403);

        $term = trim((string) $request->input('q'));

        // Too short a term would return most of the table.
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $clients = Client::query()
            ->active()
            ->where('name', 'like', '%'.$term.'%')
            // Branch admins only see their own branch's clients.
            ->when(! $user->isSuperAdmin() && $user->branch_id,
                fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name']);

        return response()->json($clients);
    }
}
