<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StoreCandidateCustomerRequest;
use App\Models\Candidate;
use App\Models\Service;
use App\Services\CandidateRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CandidateRequestController extends Controller
{
    public function __construct(protected CandidateRequestService $requests) {}

    public function create(Candidate $candidate): View|RedirectResponse
    {
        if (! $candidate->is_active) {
            throw new NotFoundHttpException();
        }

        // Only available candidates can receive new requests.
        if (! $candidate->isAvailable()) {
            return redirect()
                ->route('candidates.show', $candidate)
                ->with('error', 'هذه العاملة غير متاحة حالياً.');
        }

        $candidate->load(['nationality', 'category']);

        return view('requests.candidate', [
            'candidate' => $candidate,
            'services' => Service::query()->active()->ordered()->get(),
            'whatsappMessage' => "السلام عليكم، أرغب بالاستفسار عن العاملة رقم: {$candidate->reference_number}",
        ]);
    }

    public function store(StoreCandidateCustomerRequest $request, Candidate $candidate): RedirectResponse
    {
        if (! $candidate->is_active) {
            throw new NotFoundHttpException();
        }

        // Re-check at write time: availability may have changed since the form loaded.
        if (! $candidate->isAvailable()) {
            return redirect()
                ->route('candidates.show', $candidate)
                ->with('error', 'هذه العاملة غير متاحة حالياً.');
        }

        $candidateRequest = $this->requests->createForCandidate(
            $candidate,
            $request->validated(),
            $request,
        );

        // POST/Redirect/GET so a refresh cannot resubmit the form.
        return redirect()
            ->route('requests.success', $candidateRequest->request_number)
            ->with('success', 'تم استلام طلبك بنجاح وسيتم التواصل معك قريباً.');
    }
}
