<?php

namespace App\Enums;

enum Permission: string
{
    case ManageCandidates = 'manage_candidates';
    case ManageRequests = 'manage_requests';
    case ManageServices = 'manage_services';
    case ManageContent = 'manage_content';
    case ManageSettings = 'manage_settings';
    case ManageUsers = 'manage_users';

    public function label(): string
    {
        return match ($this) {
            self::ManageCandidates => 'إدارة السير الذاتية',
            self::ManageRequests => 'إدارة الطلبات',
            self::ManageServices => 'إدارة الخدمات',
            self::ManageContent => 'إدارة المحتوى',
            self::ManageSettings => 'إدارة الإعدادات',
            self::ManageUsers => 'إدارة المستخدمين',
        };
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
