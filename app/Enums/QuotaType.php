<?php

namespace App\Enums;

enum QuotaType: string
{
    case Vertical = 'vertical';
    case Horizontal = 'horizontal';

    public function label(): string
    {
        return match ($this) {
            self::Vertical => 'Vertical Reservation (Categorical)',
            self::Horizontal => 'Horizontal Reservation (Interlocking)',
        };
    }
}
