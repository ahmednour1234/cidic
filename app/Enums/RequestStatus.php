<?php

namespace App\Enums;

enum RequestStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Contacted => 'تم التواصل',
            self::Qualified => 'عميل مؤهل',
            self::InProgress => 'جاري التنفيذ',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::Contacted => 'info',
            self::Qualified => 'warning',
            self::InProgress => 'secondary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
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
