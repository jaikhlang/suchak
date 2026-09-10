<?php

namespace App\Enums;

enum NoticeStatus: string
{
    case Discovered = 'discovered';
    case Extracted = 'extracted';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Published = 'published';
    case Rejected = 'rejected';
    case Superseded = 'superseded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Discovered => 'Discovered',
            self::Extracted => 'Extracted',
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Superseded => 'Superseded (by Corrigendum)',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
