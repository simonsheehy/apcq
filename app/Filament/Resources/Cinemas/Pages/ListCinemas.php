<?php

namespace App\Filament\Resources\Cinemas\Pages;

use App\Filament\Actions\DownloadCinemasExcelAction;
use App\Filament\Resources\Cinemas\CinemaResource;
use App\Filament\Resources\Cinemas\Widgets\CinemaValidationStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCinemas extends ListRecords
{
    protected static string $resource = CinemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadCinemasExcelAction::make(),
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CinemaValidationStats::class,
        ];
    }
}
