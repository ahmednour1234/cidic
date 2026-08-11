<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'مدير عام',
            self::Admin => 'مدير',
            self::Staff => 'موظف',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'primary',
            self::Staff => 'secondary',
        };
    }

    /**
     * Permissions granted by this role. SuperAdmin is handled separately as a
     * blanket grant in the Gate, so it is not enumerated here.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => Permission::values(),
            self::Admin => [
                Permission::ManageCandidates->value,
                Permission::ManageRequests->value,
                Permission::ManageServices->value,
                Permission::ManageContent->value,
                Permission::ManageSettings->value,
            ],
            // Staff handle day-to-day leads only; no content or settings access.
            self::Staff => [
                Permission::ManageCandidates->value,
                Permission::ManageRequests->value,
            ],
        };
    }

    public function hasPermission(Permission|string $permission): bool
    {
        $value = $permission instanceof Permission ? $permission->value : $permission;

        return in_array($value, $this->permissions(), true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> value => Arabic label, for select inputs. */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
