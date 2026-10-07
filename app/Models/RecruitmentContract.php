<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Minimal recruitment contract. The CV panel only needs to know whether a
 * worker is bound to a live contract; the full contract workflow lives
 * outside this feature and can extend this model without touching the guards.
 */
class RecruitmentContract extends Model
{
    use HasFactory, SoftDeletes;

    /** Contract is live. */
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARRIVED = 2;
    public const STATUS_IN_SERVICE = 3;
    /** Finished; mirrors Worker::CONTRACT_ENDED. */
    public const STATUS_RETURNED = 4;
    public const STATUS_ABSCONDED = 5;

    protected $fillable = [
        'number',
        'worker_id',
        'client_id',
        'branch_id',
        'admin_id',
        'current_status',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_status' => 'integer',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function hasEnded(): bool
    {
        return in_array($this->current_status, Worker::CONTRACT_ENDED, true);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
