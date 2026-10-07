<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\Permission;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'department',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /** Super admins implicitly hold every permission. */
    public function hasPermission(Permission|string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->hasPermission($permission) ?? false;
    }

    public function assignedCandidateRequests()
    {
        return $this->hasMany(CandidateRequest::class, 'assigned_to');
    }

    public function assignedRecruitmentRequests()
    {
        return $this->hasMany(RecruitmentRequest::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ------------------------------------------------------------- CV panel

    public function isCoordination(): bool
    {
        return $this->department === Department::Coordination->value;
    }

    public function isCustomerService(): bool
    {
        return $this->department === Department::CustomerService->value;
    }

    public function isBranchManager(): bool
    {
        return $this->department === Department::BranchManager->value;
    }

    /** Super admins reach the panel regardless of department. */
    public function canAccessCvPanel(): bool
    {
        return $this->is_active
            && ($this->isSuperAdmin() || in_array($this->department, Department::values(), true));
    }

    public function nationalities(): BelongsToMany
    {
        return $this->belongsToMany(Nationality::class, 'admin_nationality', 'admin_id', 'nationality_id')
            ->withTimestamps();
    }

    /**
     * Nationality ids this user may act on, or null when unrestricted.
     *
     * Only non-super-admin coordinators are scoped; everyone else with panel
     * access sees every nationality.
     */
    public function managedNationalities(): ?array
    {
        if ($this->isSuperAdmin() || ! $this->isCoordination()) {
            return null;
        }

        return $this->nationalities()->pluck('nationalities.id')->all();
    }

    public function managesNationality(int|string|null $nationalityId): bool
    {
        if ($nationalityId === null) {
            return false;
        }

        $managed = $this->managedNationalities();

        return $managed === null || in_array((int) $nationalityId, $managed, true);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cvNotifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class, 'admin_id');
    }
}
