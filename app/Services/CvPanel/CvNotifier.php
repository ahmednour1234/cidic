<?php

namespace App\Services\CvPanel;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\AdminNotification;
use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds the panel's admin notifications.
 *
 * Nothing here may mention automatic expiry: a reservation holds until a staff
 * member releases it.
 */
final class CvNotifier
{
    public static function reserved(Worker $worker, Client $client, User $actor): void
    {
        $recipients = self::coordinatorsFor($worker->nationality_id);

        // Nobody coordinates this nationality yet, so fall back to supervisors.
        if ($recipients->isEmpty()) {
            $recipients = self::managers();
        }

        self::dispatch(
            $recipients->reject(fn (User $u) => $u->id === $actor->id),
            AdminNotification::TYPE_CV_RESERVED,
            'حجز سيرة ذاتية — بانتظار إنشاء العقد',
            sprintf(
                'حجز %s السيرة الذاتية «%s» (%s) للعميل «%s». يرجى متابعة إنشاء عقد الاستقدام.',
                $actor->name,
                $worker->name ?? ('#'.$worker->id),
                $worker->nationality?->display_name ?? '—',
                $client->name,
            ),
            self::workerUrl($worker),
        );
    }

    public static function unassigned(Worker $worker, ?Client $client, User $actor): void
    {
        // Same-branch coordination staff and managers, minus the actor.
        $recipients = User::query()
            ->active()
            ->where(function ($query) {
                $query->whereIn('department', [
                    Department::Coordination->value,
                    Department::BranchManager->value,
                ]);
            })
            ->when($actor->branch_id, fn ($q) => $q->where('branch_id', $actor->branch_id))
            ->where('id', '!=', $actor->id)
            ->get();

        self::dispatch(
            $recipients,
            AdminNotification::TYPE_UNASSIGNED,
            'إلغاء حجز عاملة',
            sprintf(
                'ألغى %s حجز السيرة الذاتية «%s»%s.',
                $actor->name,
                $worker->name ?? ('#'.$worker->id),
                $client ? sprintf(' للعميل «%s»', $client->name) : '',
            ),
            self::workerUrl($worker),
        );
    }

    public static function tamaraPaid(Worker $worker, User $actor): void
    {
        $recipients = self::coordinatorsFor($worker->nationality_id)
            ->merge(self::managers())
            ->unique('id')
            ->reject(fn (User $u) => $u->id === $actor->id);

        self::dispatch(
            $recipients,
            AdminNotification::TYPE_TAMARA_PAID,
            'تسديد عبر تمارا',
            sprintf(
                'سجّل %s تسديد تمارا للسيرة الذاتية «%s».',
                $actor->name,
                $worker->name ?? ('#'.$worker->id),
            ),
            self::workerUrl($worker),
        );
    }

    public static function cvUploaded(int $count, ?int $nationalityId, User $actor): void
    {
        self::dispatch(
            self::managers()->reject(fn (User $u) => $u->id === $actor->id),
            AdminNotification::TYPE_CV_UPLOADED,
            'رفع سير ذاتية جديدة',
            sprintf('رفع %s عدد %d سيرة ذاتية.', $actor->name, $count),
            route('cv-panel.cvs.index', array_filter(['nationality_id' => $nationalityId])),
        );
    }

    public static function assigned(Worker $worker, User $actor): void
    {
        $recipients = collect(array_filter([$worker->assignedBy]))
            ->merge(self::managers())
            ->unique('id')
            ->reject(fn (User $u) => $u->id === $actor->id);

        self::dispatch(
            $recipients,
            AdminNotification::TYPE_ASSIGNED,
            'تم تعيين عاملة',
            sprintf(
                'سجّل %s تعيين السيرة الذاتية «%s».',
                $actor->name,
                $worker->name ?? ('#'.$worker->id),
            ),
            self::workerUrl($worker),
        );
    }

    /** @param Collection<int, User> $recipients */
    private static function dispatch(
        Collection $recipients,
        string $type,
        string $title,
        string $body,
        ?string $url,
    ): void {
        if ($recipients->isEmpty()) {
            return;
        }

        try {
            $now = now();

            AdminNotification::insert($recipients->map(fn (User $user) => [
                'admin_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        } catch (Throwable $e) {
            // Never block the action being notified about.
            Log::warning('CV panel notification failed: '.$e->getMessage());
        }
    }

    /** @return Collection<int, User> */
    private static function coordinatorsFor(?int $nationalityId): Collection
    {
        if ($nationalityId === null) {
            return collect();
        }

        return User::query()
            ->active()
            ->where('department', Department::Coordination->value)
            ->whereHas('nationalities', fn ($q) => $q->where('nationalities.id', $nationalityId))
            ->get();
    }

    /** @return Collection<int, User> */
    private static function managers(): Collection
    {
        return User::query()
            ->active()
            ->where(function ($query) {
                $query->where('department', Department::BranchManager->value)
                    ->orWhere('role', UserRole::SuperAdmin->value);
            })
            ->get();
    }

    /** The panel list filtered down to this one worker. */
    private static function workerUrl(Worker $worker): string
    {
        return route('cv-panel.cvs.index', ['search' => $worker->id]);
    }
}
