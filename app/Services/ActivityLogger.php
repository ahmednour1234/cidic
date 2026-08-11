<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class ActivityLogger
{
    /**
     * Record an audit entry. Never throws: auditing must not be able to break
     * the request that triggered it.
     */
    public function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'properties' => $properties ?: null,
                'ip' => Request::ip(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Activity log write failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
