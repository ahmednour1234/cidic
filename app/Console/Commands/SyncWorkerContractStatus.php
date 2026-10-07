<?php

namespace App\Console\Commands;

use App\Models\Worker;
use App\Models\WorkerActivityLog;
use App\Services\CvPanel\WorkerActivityLogger;
use Illuminate\Console\Command;

/**
 * Realigns worker statuses with the contracts and clients they actually have.
 * Every write goes through the model so the guards stay authoritative.
 */
class SyncWorkerContractStatus extends Command
{
    protected $signature = 'workers:sync-contract-status
                            {--dry-run : Report without writing; the default}
                            {--apply : Write the changes}';

    protected $description = 'Align worker statuses with their contracts and clients';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply') && ! $this->option('dry-run');
        $changes = 0;

        // Workers on a live contract belong in assigned.
        $withOpenContract = Worker::query()
            ->whereHas('recruitmentContracts',
                fn ($q) => $q->whereNotIn('current_status', Worker::CONTRACT_ENDED))
            ->where('status', '!=', Worker::STATUS_ASSIGNED)
            ->get();

        foreach ($withOpenContract as $worker) {
            $this->line(sprintf('  #%d %s: %s → assigned', $worker->id, $worker->name ?? '—', $worker->status));
            $changes++;

            if ($apply) {
                $worker->update(['status' => Worker::STATUS_ASSIGNED]);
                $this->log($worker, 'assigned');
            }
        }

        // A client but no contract means the worker is merely held.
        $heldOnly = Worker::query()
            ->whereNotNull('client_id')
            ->whereDoesntHave('recruitmentContracts',
                fn ($q) => $q->whereNotIn('current_status', Worker::CONTRACT_ENDED))
            ->whereNotIn('status', [Worker::STATUS_RESERVED, Worker::STATUS_ASSIGNED])
            ->get();

        foreach ($heldOnly as $worker) {
            $this->line(sprintf('  #%d %s: %s → reserved', $worker->id, $worker->name ?? '—', $worker->status));
            $changes++;

            if ($apply) {
                $worker->update(['status' => Worker::STATUS_RESERVED]);
                $this->log($worker, 'reserved');
            }
        }

        if ($changes === 0) {
            $this->info('All worker statuses are already in sync.');

            return self::SUCCESS;
        }

        $this->info($apply
            ? sprintf('Updated %d worker(s).', $changes)
            : 'Dry run. Re-run with --apply to write these changes.');

        return self::SUCCESS;
    }

    private function log(Worker $worker, string $target): void
    {
        WorkerActivityLogger::log(
            $worker,
            WorkerActivityLog::ACTION_UPDATED,
            sprintf('تمت مزامنة الحالة مع العقود عبر أمر الصيانة — أصبحت «%s»', __('workers.status.'.$target)),
        );
    }
}
