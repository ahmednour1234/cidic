<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerActivityLog extends Model
{
    use HasFactory;

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_RESTORED = 'restored';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_UNASSIGNED = 'unassigned';
    public const ACTION_CV_UPLOADED = 'cv_uploaded';

    /** Shown as the actor for anything the system did on its own. */
    public const SYSTEM_ACTOR = 'النظام';

    protected $fillable = [
        'worker_id',
        'worker_name',
        'admin_id',
        'admin_name',
        'action',
        'label',
        'ip_address',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
