<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Moderator = 'moderator';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Moderator => 'Moderator',
            self::Viewer => 'Viewer',
        };
    }
}
