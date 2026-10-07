<?php

namespace App\Console\Commands;

use App\Models\Worker;
use App\Models\WorkerActivityLog;
use App\Services\CvPanel\WorkerActivityLogger;
use Illuminate\Console\Command;

/**
 * The only way a withdrawn CV returns to the public site. Withdrawal is
 * otherwise permanent, so this is always a deliberate manual act.
 */
class RestoreWithdrawnCvs extends Command
{
    protected $signature = 'workers:restore-withdrawn
                            {--nationality= : Restrict to one nationality id}
                            {--apply : Write the changes; omit for a dry run}';

    protected $description = 'Clear cv_withdrawn_at for CVs that are available, unbooked and uncontracted';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $workers = Worker::query()
            ->whereNotNull('cv_withdrawn_at')
            ->where('status', Worker::STATUS_AVAILABLE)
            ->whereNull('client_id')
            ->whereDoesntHave('recruitmentContracts')
            ->when($this->option('nationality'),
                fn ($q) => $q->where('nationality_id', (int) $this->option('nationality')))
            ->get();

        if ($workers->isEmpty()) {
            $this->info('No withdrawn CVs are eligible for restoration.');

            return self::SUCCESS;
        }

        $this->line(sprintf('%d CV(s) eligible:', $workers->count()));

        foreach ($workers as $worker) {
            $this->line(sprintf('  #%d %s', $worker->id, $worker->name ?? '—'));

            if (! $apply) {
                continue;
            }

            $worker->forceFill(['cv_withdrawn_at' => null])->save();

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_UPDATED,
                'أُعيدت السيرة الذاتية إلى الموقع العام يدويًا عبر أمر الصيانة',
            );
        }

        $this->info($apply
            ? sprintf('Restored %d CV(s).', $workers->count())
            : 'Dry run. Re-run with --apply to write these changes.');

        return self::SUCCESS;
    }
}
