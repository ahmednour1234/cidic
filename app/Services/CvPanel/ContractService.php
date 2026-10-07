<?php

namespace App\Services\CvPanel;

use App\Models\RecruitmentContract;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Turns a reservation into a contract in one step.
 *
 * There is no separate contract screen: the panel button creates the contract
 * row, moves the worker to assigned, and the row then drops out of the
 * panel's main list.
 */
final class ContractService
{
    /**
     * @throws RuntimeException when the actor may not create the contract.
     */
    public function createFromReservation(Worker $worker, User $actor): RecruitmentContract
    {
        if (! $worker->canCreateContractBy($actor)) {
            throw new RuntimeException(
                'فقط الموظف الذي أجرى الحجز أو المدير العام يمكنه إنشاء العقد، وللسير المحجوزة فقط.'
            );
        }

        $client = $worker->client;

        if ($client === null) {
            throw new RuntimeException('لا يمكن إنشاء عقد لسيرة ذاتية بدون عميل.');
        }

        if ($worker->hasActiveContract()) {
            throw new RuntimeException('يوجد عقد مرتبط بهذه العاملة بالفعل.');
        }

        return DB::transaction(function () use ($worker, $client, $actor) {
            $contract = RecruitmentContract::create([
                'number' => $this->nextNumber(),
                'worker_id' => $worker->id,
                'client_id' => $client->id,
                'branch_id' => $actor->branch_id ?? $worker->branch_id,
                'admin_id' => $actor->id,
                'current_status' => RecruitmentContract::STATUS_ACTIVE,
                'started_at' => today(),
            ]);

            // assigned is the end of the cycle; the guard allows it because a
            // live contract now exists.
            $worker->update(['status' => Worker::STATUS_ASSIGNED]);

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_ASSIGNED,
                sprintf(
                    'أُنشئ عقد استقدام رقم %s للعميل «%s» — الحالة: تم التعيين',
                    $contract->number,
                    $client->name,
                ),
                $actor,
            );

            CvNotifier::assigned($worker, $actor);

            return $contract;
        });
    }

    /** Sequential per-year number, e.g. C-2026-0001. */
    private function nextNumber(): string
    {
        $year = now()->year;

        $count = RecruitmentContract::withTrashed()
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('C-%d-%04d', $year, $count + 1);
    }
}
