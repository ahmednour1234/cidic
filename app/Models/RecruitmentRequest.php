<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'name',
        'mobile',
        'whatsapp',
        'email',
        'city',
        'service_id',
        'nationality_id',
        'candidate_category_id',
        'assigned_to',
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CandidateCategory::class, 'candidate_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', RequestStatus::New->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('request_number', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%");
        });
    }
}
