<?php

namespace App\Console\Commands;

use App\Models\Worker;
use App\Models\WorkerActivityLog;
use App\Services\CvPanel\WorkerActivityLogger;
use Illuminate\Console\Command;

/**
 * Repairs the inconsistency that direct query updates used to produce:
 * a worker shown as available while still linked to a client or a live
 * contract.
 */
class FixAvailableWithClient extends Command
{
    protected $signature = 'workers:fix-available-with-client
                            {--dry-run : Report without writing; the default}
                            {--apply : Write the changes}';

    protected $description = 'Set available workers that still have a client or an open contract back to assigned';

    public function handle(): int
    {
        // Dry run unless --apply is passed, so the command is safe to explore.
        $apply = (bool) $this->option('apply') && ! $this->option('dry-run');

        $workers = Worker::query()
            ->with(['latestContract'])
            ->where('status', Worker::STATUS_AVAILABLE)
            ->where(function ($query) {
                $query->whereNotNull('client_id')
                    ->orWhereHas('recruitmentContracts', fn ($q) => $q->whereNotIn(
                        'current_status', Worker::CONTRACT_ENDED,
                    ));
            })
            ->get();

        if ($workers->isEmpty()) {
            $this->info('No inconsistent workers found.');

            return self::SUCCESS;
        }

        $this->line(sprintf('%d inconsistent worker(s):', $workers->count()));

        foreach ($workers as $worker) {
            // A contract is the authority on who the client is when the
            // worker's own client_id was lost.
            $clientId = $worker->client_id ?? $worker->latestContract?->client_id;

            $this->line(sprintf(
                '  #%d %s — client %s',
                $worker->id,
                $worker->name ?? '—',
                $clientId ?? 'unknown',
            ));

            if (! $apply) {
                continue;
            }

            // Goes through the model, so the guards and the withdrawal stamp
            // both apply.
            $worker->update([
                'client_id' => $clientId,
                'status' => Worker::STATUS_ASSIGNED,
            ]);

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_UPDATED,
                'صُحّحت الحالة من «متاحة» إلى «تم التعيين» عبر أمر الصيانة',
            );
        }

        $this->info($apply
            ? sprintf('Fixed %d worker(s).', $workers->count())
            : 'Dry run. Re-run with --apply to write these changes.');

        return self::SUCCESS;
    }
}
