<?php

namespace App\Models;

use App\Support\CvPanel\SearchableHash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'national_id',
        'classification',
        'branch_id',
        'admin_id',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'phone' => 'encrypted',
            'national_id' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            if ($client->isDirty('phone')) {
                $client->phone_hash = SearchableHash::make($client->phone);
            }

            if ($client->isDirty('national_id')) {
                $client->national_id_hash = SearchableHash::make($client->national_id);
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function recruitmentContracts(): HasMany
    {
        return $this->hasMany(RecruitmentContract::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
