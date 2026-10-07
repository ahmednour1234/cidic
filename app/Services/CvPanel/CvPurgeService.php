<?php

namespace App\Services\CvPanel;

use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Clears a nationality's stale CVs when a fresh batch is uploaded.
 *
 * Only genuinely free rows qualify: anything reserved, held for a client, or
 * bound to a contract is never touched. Deletes are soft, so the row and its
 * file survive.
 */
final class CvPurgeService
{
    public function candidates(int $nationalityId): Builder
    {
        return Worker::query()
            ->where('nationality_id', $nationalityId)
            ->whereNotNull('cv_path')
            ->where('status', Worker::STATUS_AVAILABLE)
            ->whereNull('client_id')
            ->whereDoesntHave('recruitmentContracts');
    }

    public function countFor(?int $nationalityId): int
    {
        return $nationalityId === null ? 0 : $this->candidates($nationalityId)->count();
    }

    /** @return int the number of CVs soft-deleted. */
    public function purge(int $nationalityId, ?User $actor = null): int
    {
        return DB::transaction(function () use ($nationalityId, $actor) {
            // Re-queried inside the transaction so anything reserved since the
            // upload page was rendered drops out of the set.
            $workers = $this->candidates($nationalityId)->lockForUpdate()->get();

            foreach ($workers as $worker) {
                $worker->delete();

                WorkerActivityLogger::log(
                    $worker,
                    WorkerActivityLog::ACTION_DELETED,
                    'حُذفت ضمن تنظيف سير الجنسية عند رفع دفعة جديدة (حذف ناعم)',
                    $actor,
                );
            }

            return $workers->count();
        });
    }
}
