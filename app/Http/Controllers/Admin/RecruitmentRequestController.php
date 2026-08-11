<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\RecruitmentRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentRequestController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $requests = RecruitmentRequest::query()
            ->with(['service:id,title', 'nationality:id,name_ar', 'category:id,name_ar', 'assignee:id,name'])
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.recruitment-requests.index', [
            'requests' => $requests,
            'statusOptions' => RequestStatus::options(),
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(RecruitmentRequest $recruitmentRequest): View
    {
        $recruitmentRequest->load(['service', 'nationality', 'category', 'assignee:id,name']);

        return view('admin.recruitment-requests.show', [
            'request' => $recruitmentRequest,
            'statusOptions' => RequestStatus::options(),
            'staff' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, RecruitmentRequest $recruitmentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $recruitmentRequest->update($validated);

        return back()->with('success', 'تم حفظ بيانات الطلب.');
    }

    public function updateStatus(Request $request, RecruitmentRequest $recruitmentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(RequestStatus::values())],
        ]);

        $recruitmentRequest->update($validated);

        $this->activity->log('recruitment_request.status_changed', $recruitmentRequest, [
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function destroy(RecruitmentRequest $recruitmentRequest): RedirectResponse
    {
        $recruitmentRequest->delete();

        return redirect()
            ->route('admin.recruitment-requests.index')
            ->with('success', 'تم حذف الطلب.');
    }
}
