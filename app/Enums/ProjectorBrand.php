<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectorBrand: string implements HasLabel
{
    case Barco = 'barco';
    case Christie = 'christie';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Barco => 'Barco',
            self::Christie => 'Christie',
            self::Other => 'Autre',
        };
    }
}
