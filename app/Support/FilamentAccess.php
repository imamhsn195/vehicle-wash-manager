<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FilamentAccess
{
    public static function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return list<int>
     */
    public static function managedSiteIds(?User $user = null): array
    {
        $user ??= static::user();

        if (! $user) {
            return [];
        }

        return $user->managedSites()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return list<int>
     */
    public static function staffSiteIds(?User $user = null): array
    {
        $user ??= static::user();
        $staff = $user?->staffProfile;

        if (! $staff) {
            return [];
        }

        return $staff->assignments()->pluck('site_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public static function isAdmin(?User $user = null): bool
    {
        return ($user ?? static::user())?->isAdmin() ?? false;
    }

    public static function isAccountant(?User $user = null): bool
    {
        return ($user ?? static::user())?->role === UserRole::Accountant;
    }

    public static function isSiteManager(?User $user = null): bool
    {
        return ($user ?? static::user())?->isSiteManager() ?? false;
    }

    public static function isStaff(?User $user = null): bool
    {
        return ($user ?? static::user())?->role === UserRole::Staff;
    }

    public static function isPartner(?User $user = null): bool
    {
        return ($user ?? static::user())?->role === UserRole::Partner;
    }

    public static function canAccessSites(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessStaff(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessDailyLogs(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessDailyLogEntry(): bool
    {
        return static::isAdmin()
            || static::isAccountant()
            || static::isSiteManager()
            || static::isStaff();
    }

    public static function canAccessExpenses(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canApproveExpenses(): bool
    {
        return static::isAdmin() || static::isAccountant();
    }

    public static function canAccessCashReconciliation(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessEquipment(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessFinanceReports(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isSiteManager();
    }

    public static function canAccessExports(): bool
    {
        return static::isAdmin() || static::isAccountant();
    }

    public static function canAccessPartners(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isPartner();
    }

    public static function canAccessPartnerPayouts(): bool
    {
        return static::isAdmin() || static::isAccountant() || static::isPartner();
    }

    public static function canAccessPayroll(): bool
    {
        return static::isAdmin() || static::isAccountant();
    }

    public static function canAccessCurrency(): bool
    {
        return static::isAdmin();
    }

    public static function canEditPayFields(): bool
    {
        return static::isAdmin() || static::isAccountant();
    }
}
