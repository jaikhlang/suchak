<?php

namespace App\Enums;

enum EmploymentType: string
{
    case Permanent = 'permanent';
    case Contractual = 'contractual';
    case Deputation = 'deputation';
    case Apprentice = 'apprentice';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent / Regular',
            self::Contractual => 'Contractual / Ad-hoc',
            self::Deputation => 'Deputation',
            self::Apprentice => 'Apprentice (Training)',
        };
    }
}
