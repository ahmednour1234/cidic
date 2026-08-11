<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\Candidate;
use App\Models\CandidateRequest;
use App\Models\CandidateRequestStatusHistory;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidateRequestService
{
    /** Window used to collapse accidental repeat submissions. */
    protected const DUPLICATE_WINDOW_MINUTES = 10;

    public function __construct(protected ReferenceNumberService $references) {}

    /**
     * Store a request for a specific candidate.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForCandidate(Candidate $candidate, array $data, Request $request): CandidateRequest
    {
        // Collapse a genuine double-submit (same candidate + mobile, minutes apart)
        // into the original record instead of creating a second lead.
        $existing = CandidateRequest::query()
            ->where('candidate_id', $candidate->id)
            ->where('mobile', $data['mobile'])
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($candidate, $data, $request) {
            $candidateRequest = CandidateRequest::create([
                ...$data,
                'request_number' => 'TMP-' . bin2hex(random_bytes(8)),
                'candidate_id' => $candidate->id,
                'status' => RequestStatus::New,
                'source' => 'website',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            $this->references->assignCandidateRequestNumber($candidateRequest);

            CandidateRequestStatusHistory::create([
                'candidate_request_id' => $candidateRequest->id,
                'old_status' => null,
                'new_status' => RequestStatus::New->value,
                'notes' => 'تم استلام الطلب من الموقع.',
            ]);

            return $candidateRequest->refresh();
        });
    }

    /**
     * Store a general recruitment request (no specific candidate).
     *
     * @param  array<string, mixed>  $data
     */
    public function createGeneral(array $data, Request $request): RecruitmentRequest
    {
        $existing = RecruitmentRequest::query()
            ->where('mobile', $data['mobile'])
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($data, $request) {
            $recruitmentRequest = RecruitmentRequest::create([
                ...$data,
                'request_number' => 'TMP-' . bin2hex(random_bytes(8)),
                'status' => RequestStatus::New,
                'source' => 'website',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            $this->references->assignRecruitmentRequestNumber($recruitmentRequest);

            return $recruitmentRequest->refresh();
        });
    }

    /**
     * Change a request status and append to its history.
     */
    public function changeStatus(
        CandidateRequest $request,
        RequestStatus $status,
        ?int $userId = null,
        ?string $notes = null,
    ): CandidateRequest {
        return DB::transaction(function () use ($request, $status, $userId, $notes) {
            $old = $request->status;

            if ($old === $status && blank($notes)) {
                return $request;
            }

            $request->update(['status' => $status]);

            CandidateRequestStatusHistory::create([
                'candidate_request_id' => $request->id,
                'old_status' => $old?->value,
                'new_status' => $status->value,
                'changed_by' => $userId,
                'notes' => $notes,
            ]);

            return $request->refresh();
        });
    }
}
