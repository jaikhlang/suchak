<?php

namespace App\Enums;

enum TrustLevel: string
{
    case OfficialVerified = 'official_verified';
    case InstitutionalVerified = 'institutional_verified';
    case PublicSectorVerified = 'public_sector_verified';
    case DiscoveryOnly = 'discovery_only';

    public function label(): string
    {
        return match ($this) {
            self::OfficialVerified => 'Official Verified (.gov.in / .nic.in)',
            self::InstitutionalVerified => 'Institutional Verified (.ac.in / .edu.in)',
            self::PublicSectorVerified => 'Public Sector Verified',
            self::DiscoveryOnly => 'Discovery Only (Aggregator)',
        };
    }

    public function canAutoPublish(): bool
    {
        return match ($this) {
            self::OfficialVerified, self::InstitutionalVerified => true,
            default => false,
        };
    }
}
