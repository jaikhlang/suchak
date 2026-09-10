<?php

namespace App\Enums;

enum ReservationCategory: string
{
    case UR = 'UR';
    case OBC_NCL = 'OBC_NCL';
    case SC = 'SC';
    case ST = 'ST';
    case EWS = 'EWS';
    case PWBD_OH = 'PWBD_OH';
    case PWBD_HH = 'PWBD_HH';
    case PWBD_VH = 'PWBD_VH';
    case PWBD_OTHER = 'PWBD_OTHER';
    case EX_SERVICEMEN = 'EX_SERVICEMEN';
    case WOMEN = 'WOMEN';

    public function label(): string
    {
        return match ($this) {
            self::UR => 'Unreserved (General / Open)',
            self::OBC_NCL => 'Other Backward Classes (OBC - NCL)',
            self::SC => 'Scheduled Castes (SC)',
            self::ST => 'Scheduled Tribes (ST)',
            self::EWS => 'Economically Weaker Sections (EWS)',
            self::PWBD_OH => 'PwBD (Locomotor / Orthopedically Handicapped)',
            self::PWBD_HH => 'PwBD (Deaf & Hard of Hearing)',
            self::PWBD_VH => 'PwBD (Blindness & Low Vision)',
            self::PWBD_OTHER => 'PwBD (Autism / Intellectual / Multiple)',
            self::EX_SERVICEMEN => 'Ex-Servicemen (ESM)',
            self::WOMEN => 'Women Quota (Horizontal)',
        };
    }

    public function defaultQuotaType(): QuotaType
    {
        return match ($this) {
            self::UR, self::OBC_NCL, self::SC, self::ST, self::EWS => QuotaType::Vertical,
            default => QuotaType::Horizontal,
        };
    }
}
