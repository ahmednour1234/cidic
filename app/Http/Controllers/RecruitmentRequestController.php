<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StoreRecruitmentRequest;
use App\Models\CandidateCategory;
use App\Models\CandidateRequest;
use App\Models\Nationality;
use App\Models\RecruitmentRequest;
use App\Models\Service;
use App\Services\CandidateRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecruitmentRequestController extends Controller
{
    public function __construct(protected CandidateRequestService $requests) {}

    public function create(): View
    {
        return view('requests.general', [
            'services' => Service::query()->active()->ordered()->get(),
            'nationalities' => Nationality::query()->active()->ordered()->get(),
            'categories' => CandidateCategory::query()->active()->ordered()->get(),
        ]);
    }

    public function store(StoreRecruitmentRequest $request): RedirectResponse
    {
        $recruitmentRequest = $this->requests->createGeneral($request->validated(), $request);

        return redirect()
            ->route('requests.success', $recruitmentRequest->request_number)
            ->with('success', 'تم استلام طلبك بنجاح وسيتم التواصل معك قريباً.');
    }

    /**
     * Confirmation screen. The request number is looked up across both request
     * types so a single success route serves either flow.
     */
    public function success(string $number): View
    {
        $request = CandidateRequest::query()->where('request_number', $number)->first()
            ?? RecruitmentRequest::query()->where('request_number', $number)->first();

        abort_if($request === null, 404);

        return view('requests.success', [
            'requestNumber' => $request->request_number,
        ]);
    }
}
