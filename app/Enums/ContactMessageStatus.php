<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديدة',
            self::Read => 'مقروءة',
            self::Replied => 'تم الرد',
            self::Closed => 'مغلقة',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::Read => 'info',
            self::Replied => 'success',
            self::Closed => 'secondary',
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
