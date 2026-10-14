<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingProgressStatus: string implements HasColor, HasLabel
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotStarted => 'Non commencée',
            self::InProgress => 'En cours',
            self::Completed => 'Complétée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NotStarted => 'gray',
            self::InProgress => 'warning',
            self::Completed => 'success',
        };
    }
}
