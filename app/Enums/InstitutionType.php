<?php

namespace App\Enums;

enum InstitutionType: string
{
    case CentralGov = 'central_gov';
    case StateGov = 'state_gov';
    case Psu = 'psu';
    case Autonomous = 'autonomous';
    case Banking = 'banking';
    case Defence = 'defence';
    case Court = 'court';

    public function label(): string
    {
        return match ($this) {
            self::CentralGov => 'Central Government',
            self::StateGov => 'State Government',
            self::Psu => 'Public Sector Undertaking (PSU)',
            self::Autonomous => 'Autonomous Body / University',
            self::Banking => 'Banking & Insurance',
            self::Defence => 'Defence & Paramilitary',
            self::Court => 'Judicial / Court',
        };
    }
}
