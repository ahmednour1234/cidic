<?php

namespace App\Support\CvPanel;

use App\Models\User;
use App\Models\Worker;

/**
 * The CV panel's permission matrix, kept in one place so the controllers,
 * the views and the tests all read the same rules.
 */
final class CvPanelPermissions
{
    /** Uploading is for coordinators, within their own nationalities. */
    public static function canUpload(?User $user): bool
    {
        return self::active($user) && ($user->isSuperAdmin() || $user->isCoordination());
    }

    /** Reserving is for customer service and managers - never coordinators. */
    public static function canReserve(?User $user): bool
    {
        return self::active($user)
            && ($user->isSuperAdmin() || $user->isCustomerService() || $user->isBranchManager());
    }

    public static function canSearchClients(?User $user): bool
    {
        return self::canReserve($user);
    }

    /** Deleting is for coordinators, within their own nationalities. */
    public static function canDelete(?User $user): bool
    {
        return self::active($user) && ($user->isSuperAdmin() || $user->isCoordination());
    }

    /** The reserved follow-up screen and "mark assigned" are coordinator work. */
    public static function canFollowUpReserved(?User $user): bool
    {
        return self::active($user) && ($user->isSuperAdmin() || $user->isCoordination());
    }

    /** Managing users and coordinator-nationality links. */
    public static function canManageUsers(?User $user): bool
    {
        return self::active($user) && ($user->isSuperAdmin() || $user->isBranchManager());
    }

    /**
     * Whether this worker may be soft-deleted: available, unbooked, and with
     * no contract. A booked CV is never deleted.
     */
    public static function workerIsDeletable(Worker $worker): bool
    {
        return $worker->status === Worker::STATUS_AVAILABLE
            && $worker->client_id === null
            && ! $worker->hasActiveContract();
    }

    public static function canDeleteWorker(?User $user, Worker $worker): bool
    {
        return self::canDelete($user)
            && $user->managesNationality($worker->nationality_id)
            && self::workerIsDeletable($worker);
    }

    public static function canUploadToNationality(?User $user, int|string|null $nationalityId): bool
    {
        return self::canUpload($user) && $user->managesNationality($nationalityId);
    }

    /** Nationality ids the user may see, or null when unrestricted. */
    public static function visibleNationalityIds(?User $user): ?array
    {
        return $user?->managedNationalities();
    }

    private static function active(?User $user): bool
    {
        return $user !== null && $user->is_active && $user->canAccessCvPanel();
    }
}
