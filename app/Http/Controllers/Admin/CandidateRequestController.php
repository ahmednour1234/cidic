<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\CandidateRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\CandidateRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateRequestController extends Controller
{
    public function __construct(
        protected CandidateRequestService $requests,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $candidateRequests = CandidateRequest::query()
            ->with(['candidate:id,name,reference_number,profile_image', 'assignee:id,name'])
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.requests.index', [
            'requests' => $candidateRequests,
            'statusOptions' => RequestStatus::options(),
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(CandidateRequest $candidateRequest): View
    {
        $candidateRequest->load([
            'candidate.nationality',
            'assignee:id,name',
            'statusHistories.changedBy:id,name',
        ]);

        return view('admin.requests.show', [
            'request' => $candidateRequest,
            'statusOptions' => RequestStatus::options(),
            'staff' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Update internal notes / assignment. */
    public function update(Request $request, CandidateRequest $candidateRequest): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $candidateRequest->update($validated);

        return back()->with('success', 'تم حفظ بيانات الطلب.');
    }

    public function updateStatus(Request $request, CandidateRequest $candidateRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(RequestStatus::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->requests->changeStatus(
            $candidateRequest,
            RequestStatus::from($validated['status']),
            $request->user()->id,
            $validated['notes'] ?? null,
        );

        $this->activity->log('request.status_changed', $candidateRequest, [
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function destroy(CandidateRequest $candidateRequest): RedirectResponse
    {
        $candidateRequest->delete();

        return redirect()
            ->route('admin.candidate-requests.index')
            ->with('success', 'تم حذف الطلب.');
    }
}
