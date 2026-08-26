<?php

namespace App\Filament\Resources\Cinemas\Widgets;

use App\Models\Cinema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class CinemaValidationStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Validation des informations';

    protected function getStats(): array
    {
        $total = Cinema::query()->count();
        $validated = Cinema::query()->informationValidated()->count();
        $pending = $total - $validated;
        $percentage = $total > 0
            ? Number::percentage($validated / $total * 100, precision: 0)
            : Number::percentage(0, precision: 0);

        return [
            Stat::make('Validés', Number::format($validated))
                ->description($percentage.' du parc')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success'),
            Stat::make('Non validés', Number::format($pending))
                ->description('En attente de confirmation')
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning'),
            Stat::make('Total', Number::format($total))
                ->description('Cinémas actifs')
                ->color('gray'),
        ];
    }
}
