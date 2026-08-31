<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectionType: string implements HasLabel
{
    case Standard = 'standard';
    case LaserPhosphor = 'laser_phosphor';
    case LaserRetrofit = 'laser_retrofit';
    case LaserRGB = 'laser_rgb';

    public function getLabel(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::LaserPhosphor => 'Laser Phosphore',
            self::LaserRetrofit => 'Laser Retrofit',
            self::LaserRGB => 'Laser RGB',
        };
    }
}
