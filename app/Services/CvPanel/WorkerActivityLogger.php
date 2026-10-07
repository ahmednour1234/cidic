<?php

namespace App\Services\CvPanel;

use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Writes the worker audit trail.
 *
 * Every write is swallowed on failure: logging must never block the action it
 * is recording.
 */
final class WorkerActivityLogger
{
    public static function log(
        ?Worker $worker,
        string $action,
        string $label,
        ?User $actor = null,
    ): void {
        try {
            WorkerActivityLog::create([
                'worker_id' => $worker?->id,
                'worker_name' => $worker?->name,
                'admin_id' => $actor?->id,
                'admin_name' => $actor?->name ?? WorkerActivityLog::SYSTEM_ACTOR,
                'action' => $action,
                'label' => $label,
                'ip_address' => self::clientIp(),
            ]);
        } catch (Throwable $e) {
            // Deliberately non-fatal.
            Log::warning('Worker activity log failed: '.$e->getMessage());
        }
    }

    private static function clientIp(): ?string
    {
        try {
            return Request::ip();
        } catch (Throwable) {
            // No request bound, e.g. inside a console command.
            return null;
        }
    }
}
