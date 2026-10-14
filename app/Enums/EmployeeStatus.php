<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatus: string implements HasColor, HasLabel
{
    case Invited = 'invited';
    case Active = 'active';
    case Deactivated = 'deactivated';

    public function getLabel(): string
    {
        return match ($this) {
            self::Invited => 'Invitation envoyée',
            self::Active => 'Actif',
            self::Deactivated => 'Désactivé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Invited => 'warning',
            self::Active => 'success',
            self::Deactivated => 'gray',
        };
    }
}
