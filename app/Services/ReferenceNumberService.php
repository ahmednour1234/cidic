<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\CandidateRequest;
use App\Models\RecruitmentRequest;

/**
 * Identifier generation.
 *
 * Numbers are derived from the row's auto-increment id *after* insert, so two
 * concurrent writers can never derive the same value. count()+1 is deliberately
 * avoided because it races and reuses numbers after deletions.
 */
class ReferenceNumberService
{
    public function candidateReference(int $id): string
    {
        return 'CV-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    public function requestNumber(int $id, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return 'REQ-' . $year . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Assign a reference to a freshly created candidate.
     * Uses the persisted id, guaranteeing uniqueness against the unique index.
     */
    public function assignCandidateReference(Candidate $candidate): string
    {
        $reference = $this->candidateReference($candidate->id);

        $candidate->forceFill(['reference_number' => $reference])->saveQuietly();

        return $reference;
    }

    public function assignCandidateRequestNumber(CandidateRequest $request): string
    {
        $number = $this->requestNumber($request->id, (int) $request->created_at?->format('Y') ?: null);

        $request->forceFill(['request_number' => $number])->saveQuietly();

        return $number;
    }

    public function assignRecruitmentRequestNumber(RecruitmentRequest $request): string
    {
        $number = $this->requestNumber($request->id, (int) $request->created_at?->format('Y') ?: null);

        $request->forceFill(['request_number' => $number])->saveQuietly();

        return $number;
    }
}
