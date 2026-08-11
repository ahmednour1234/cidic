<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case UnderProcess = 'under_process';
    case Hired = 'hired';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'متاحة',
            self::Reserved => 'محجوزة',
            self::UnderProcess => 'جاري الإجراءات',
            self::Hired => 'تم التعاقد',
            self::Unavailable => 'غير متاحة',
        };
    }

    /** Bootstrap contextual suffix used to build badge classes. */
    public function badge(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Reserved => 'warning',
            self::UnderProcess => 'info',
            self::Hired => 'secondary',
            self::Unavailable => 'danger',
        };
    }

    /** Only available candidates may receive new customer requests. */
    public function isRequestable(): bool
    {
        return $this === self::Available;
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
