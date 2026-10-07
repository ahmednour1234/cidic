<?php

namespace App\Models;

use App\Support\CvPanel\SearchableHash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Worker extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_ASSIGNED = 'assigned';

    /** Statuses that mean the worker is held for a client. */
    public const BOOKED_STATUSES = [self::STATUS_RESERVED, self::STATUS_ASSIGNED];

    /** Contract statuses that count as finished: returned, absconded. */
    public const CONTRACT_ENDED = [4, 5];

    /**
     * Nominal reservation window, for display only. Reservations never expire
     * on their own - a staff member must release them.
     */
    public const RESERVATION_HOURS = 72;
    public const RESERVATION_HOURS_TAMARA = 120;

    protected $fillable = [
        'name',
        'passport_number',
        'phone',
        'nationality_id',
        'profession',
        'experience',
        'religion',
        'gender',
        'age',
        'cv_path',
        'cv_disk',
        'original_cv_name',
        'cv_withdrawn_at',
        'status',
        'client_id',
        'assigned_by_admin_id',
        'assigned_at',
        'tamara_paid_at',
        'tamara_paid_by_admin_id',
        'admin_id',
        'branch_id',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'passport_number' => 'encrypted',
            'phone' => 'encrypted',
            'cv_withdrawn_at' => 'datetime',
            'assigned_at' => 'datetime',
            'tamara_paid_at' => 'datetime',
            'active' => 'boolean',
            'age' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Keep the searchable digests in step with the encrypted columns.
        static::saving(function (Worker $worker) {
            if ($worker->isDirty('passport_number')) {
                $worker->passport_number_hash = SearchableHash::make($worker->passport_number);
            }

            if ($worker->isDirty('phone')) {
                $worker->phone_hash = SearchableHash::make($worker->phone);
            }
        });

        // (1) Stamp the public withdrawal once, the first time the worker is
        // booked. Never cleared automatically - only workers:restore-withdrawn
        // may clear it.
        static::saving(function (Worker $worker) {
            if ($worker->cv_withdrawn_at !== null) {
                return;
            }

            if (in_array($worker->status, self::BOOKED_STATUSES, true) || $worker->client_id !== null) {
                $worker->cv_withdrawn_at = now();
            }
        });

        // (2) Guard the status transitions. Every status change must go through
        // the model: a direct Worker::where()->update() bypasses this and has
        // historically produced workers shown as available while linked to a
        // client.
        static::updating(function (Worker $worker) {
            if (! $worker->isDirty('status')) {
                return;
            }

            if ($worker->status === self::STATUS_AVAILABLE) {
                $contractStatus = $worker->latestContract?->current_status;

                if ($contractStatus !== null && ! in_array((int) $contractStatus, self::CONTRACT_ENDED, true)) {
                    $worker->status = self::STATUS_ASSIGNED;

                    return;
                }

                // A client that is still linked means this is not a real
                // release, unless the same update clears client_id - that is
                // the legitimate unassign path.
                if ($worker->client_id !== null && ! $worker->isDirty('client_id')) {
                    $worker->status = self::STATUS_ASSIGNED;
                }

                return;
            }

            // reserved/assigned with neither a client nor a contract would
            // leave a stuck row, so revert it.
            if (in_array($worker->status, self::BOOKED_STATUSES, true)
                && ! $worker->client_id
                && ! $worker->hasActiveContract()) {
                $worker->status = $worker->getOriginal('status');
            }
        });
    }

    // ----------------------------------------------------------------- state

    public function isBooked(): bool
    {
        return $this->client_id !== null || in_array($this->status, self::BOOKED_STATUSES, true);
    }

    /** The worker's own client, falling back to the latest contract's client. */
    public function effectiveClient(): ?Client
    {
        return $this->client ?? $this->latestContract?->client;
    }

    public function hasActiveContract(): bool
    {
        // Uses the eager-loaded relation when it is already present, so the
        // guards do not fire a query per saved row.
        $contract = $this->relationLoaded('latestContract')
            ? $this->getRelation('latestContract')
            : $this->latestContract()->first();

        return $contract !== null;
    }

    public function cvDisk(): string
    {
        // Null means the legacy public disk, from before CVs moved to private.
        return $this->cv_disk ?: 'public';
    }

    public function hasCvFile(): bool
    {
        if (! $this->cv_path) {
            return false;
        }

        return Storage::disk($this->cvDisk())->exists($this->cv_path);
    }

    /** Display only; no reservation is ever released on a timer. */
    public function reservationHours(): int
    {
        return $this->tamara_paid_at ? self::RESERVATION_HOURS_TAMARA : self::RESERVATION_HOURS;
    }

    public function isWithdrawn(): bool
    {
        return $this->cv_withdrawn_at !== null;
    }

    // ----------------------------------------------------------- permissions

    /** Only the reserver or a super admin may act on a live reservation. */
    private function canActOnReservationBy(?User $admin): bool
    {
        if (! $admin || ! $admin->is_active) {
            return false;
        }

        if ($this->status !== self::STATUS_RESERVED) {
            return false;
        }

        return $admin->isSuperAdmin() || $this->assigned_by_admin_id === $admin->id;
    }

    public function canBeUnassignedBy(?User $admin): bool
    {
        // A live contract is unlinked from the contract screen, never here.
        if ($this->hasActiveContract()) {
            return false;
        }

        return $this->canActOnReservationBy($admin);
    }

    public function canRecordTamaraBy(?User $admin): bool
    {
        if ($this->tamara_paid_at !== null) {
            return false;
        }

        return $this->canActOnReservationBy($admin);
    }

    public function canCreateContractBy(?User $admin): bool
    {
        return $this->canActOnReservationBy($admin);
    }

    // ------------------------------------------------------------- relations

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id');
    }

    public function tamaraPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tamara_paid_by_admin_id');
    }

    public function latestContract(): HasOne
    {
        return $this->hasOne(RecruitmentContract::class)->latestOfMany();
    }

    public function recruitmentContracts(): HasMany
    {
        return $this->hasMany(RecruitmentContract::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(WorkerActivityLog::class);
    }

    // ---------------------------------------------------------------- scopes

    /** Exactly the set the public site shows. */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('active', true)
            ->where('status', self::STATUS_AVAILABLE)
            ->whereNotNull('cv_path')
            ->whereNull('cv_withdrawn_at');
    }

    public function scopeForNationalities(Builder $query, ?array $nationalityIds): Builder
    {
        // Null means unrestricted: super admins and non-coordination staff.
        return $nationalityIds === null
            ? $query
            : $query->whereIn('nationality_id', $nationalityIds);
    }
}
