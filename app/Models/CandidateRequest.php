<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'candidate_id',
        'assigned_to',
        'customer_name',
        'mobile',
        'whatsapp',
        'email',
        'city',
        'service_type',
        'notes',
        'admin_notes',
        'status',
        'source',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CandidateRequestStatusHistory::class)->latest('created_at');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', RequestStatus::New->value);
    }

    /** Search across request number, customer name, mobile and candidate reference. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('request_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhereHas('candidate', function (Builder $c) use ($term) {
                    $c->where('reference_number', 'like', "%{$term}%");
                });
        });
    }
}
