<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateRequestStatusHistory extends Model
{
    use HasFactory;

    /** Only created_at is tracked; history rows are never updated. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'candidate_request_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => RequestStatus::class,
            'new_status' => RequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CandidateRequest::class, 'candidate_request_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
