<?php

namespace App\Services\CvPanel;

use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CvReservationService
{
    /**
     * Hold a CV for a client.
     *
     * The row is re-read under a pessimistic lock inside the transaction, so
     * two agents reserving the same CV at once cannot both succeed.
     *
     * @throws RuntimeException when the CV is no longer available.
     */
    public function reserve(Worker $worker, Client $client, User $actor): Worker
    {
        return DB::transaction(function () use ($worker, $client, $actor) {
            $fresh = Worker::whereKey($worker->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== Worker::STATUS_AVAILABLE
                || $fresh->client_id !== null
                || ! $fresh->active) {
                throw new RuntimeException('هذه السيرة الذاتية لم تعد متاحة للحجز.');
            }

            // The saving guard stamps cv_withdrawn_at as part of this write.
            $fresh->update([
                'client_id' => $client->id,
                'status' => Worker::STATUS_RESERVED,
                'assigned_by_admin_id' => $actor->id,
                'assigned_at' => now(),
            ]);

            WorkerActivityLogger::log(
                $fresh,
                WorkerActivityLog::ACTION_ASSIGNED,
                sprintf('حجز السيرة الذاتية للعميل «%s» عبر لوحة السير الذاتية', $client->name),
                $actor,
            );

            CvNotifier::reserved($fresh, $client, $actor);

            return $fresh;
        });
    }

    /**
     * Release a reservation. The CV stays withdrawn from the public site.
     *
     * @throws RuntimeException when the actor may not release this row.
     */
    public function cancel(Worker $worker, User $actor): Worker
    {
        if ($worker->hasActiveContract()) {
            throw new RuntimeException(
                'لا يمكن فكّ الحجز من هنا لوجود عقد مرتبط — يتم فك الارتباط من صفحة العقد.'
            );
        }

        if (! $worker->canBeUnassignedBy($actor)) {
            throw new RuntimeException(
                'فقط الموظف الذي أجرى الحجز أو المدير العام يمكنه إلغاء هذا الحجز.'
            );
        }

        return DB::transaction(function () use ($worker, $actor) {
            $client = $worker->client;
            $previousStatus = $worker->status;
            $reserver = $worker->assignedBy;
            $reservedAt = $worker->assigned_at;

            // Every release clears the holder fields in the same write, which
            // is what tells the updating guard this is a genuine release.
            $worker->update([
                'status' => Worker::STATUS_AVAILABLE,
                'client_id' => null,
                'assigned_by_admin_id' => null,
                'assigned_at' => null,
            ]);

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_UNASSIGNED,
                sprintf(
                    'تم فكّ حجز العاملة من العميل «%s» — الحالة: %s ← متاحة (كان الحجز بواسطة %s بتاريخ %s)',
                    $client?->name ?? '—',
                    $previousStatus,
                    $reserver?->name ?? '—',
                    $reservedAt?->format('Y-m-d H:i') ?? '—',
                ),
                $actor,
            );

            CvNotifier::unassigned($worker, $client, $actor);

            return $worker;
        });
    }

    /**
     * Record a Tamara payment. Status and client are untouched.
     *
     * @throws RuntimeException when the actor may not record it.
     */
    public function recordTamara(Worker $worker, User $actor): Worker
    {
        if (! $worker->canRecordTamaraBy($actor)) {
            throw new RuntimeException('لا يمكن تسجيل تسديد تمارا لهذه السيرة الذاتية.');
        }

        return DB::transaction(function () use ($worker, $actor) {
            $worker->update([
                'tamara_paid_at' => now(),
                'tamara_paid_by_admin_id' => $actor->id,
            ]);

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_UPDATED,
                sprintf('تم تسجيل تسديد عبر تمارا بواسطة %s', $actor->name),
                $actor,
            );

            CvNotifier::tamaraPaid($worker, $actor);

            return $worker;
        });
    }

    /**
     * Coordinator marks a reserved CV as assigned - the end of the cycle.
     *
     * @throws RuntimeException when the row is not reserved.
     */
    public function markAssigned(Worker $worker, User $actor): Worker
    {
        if ($worker->status !== Worker::STATUS_RESERVED) {
            throw new RuntimeException('يمكن تعيين السير المحجوزة فقط.');
        }

        return DB::transaction(function () use ($worker, $actor) {
            $worker->update(['status' => Worker::STATUS_ASSIGNED]);

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_ASSIGNED,
                sprintf('تم تعيين العاملة للعميل «%s»', $worker->client?->name ?? '—'),
                $actor,
            );

            CvNotifier::assigned($worker, $actor);

            return $worker;
        });
    }

    /**
     * Resolve the client for a reservation, creating one inline when the agent
     * supplied a name and phone instead of picking an existing record.
     *
     * @throws RuntimeException when neither a client nor a name+phone is given.
     */
    public function resolveClient(?int $clientId, ?string $name, ?string $phone, User $actor): Client
    {
        if ($clientId !== null) {
            $client = Client::find($clientId);

            if ($client) {
                return $client;
            }
        }

        $name = trim((string) $name);
        $phone = trim((string) $phone);

        // WhatsApp leads are usually unregistered; a name and phone is enough.
        if ($name === '' || $phone === '') {
            throw new RuntimeException('اختر عميلاً مسجّلاً أو أدخل اسم العميل ورقم جواله.');
        }

        return Client::create([
            'name' => $name,
            'phone' => $phone,
            'classification' => 'confirmed',
            'branch_id' => $actor->branch_id,
            'admin_id' => $actor->id,
        ]);
    }
}
