<?php

namespace App\Enums;

enum SourceStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Failing = 'failing';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Failing => 'Failing',
            self::Archived => 'Archived',
        };
    }
}
