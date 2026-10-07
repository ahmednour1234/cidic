<?php

namespace App\Enums;

/**
 * The CV panel's own role axis, stored on users.department. It sits alongside
 * UserRole, which keeps governing the main admin area: a user with no
 * department is simply not CV-panel staff.
 */
enum Department: string
{
    case Coordination = 'coordination';
    case CustomerService = 'customer_service';
    case BranchManager = 'branch_manager';

    public function label(): string
    {
        return match ($this) {
            self::Coordination => 'التنسيق',
            self::CustomerService => 'خدمة العملاء',
            self::BranchManager => 'مدير فرع',
        };
    }

    /** @return list<string> */
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
