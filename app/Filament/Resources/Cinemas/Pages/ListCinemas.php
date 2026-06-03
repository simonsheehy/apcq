<?php

namespace App\Filament\Resources\Cinemas\Pages;

use App\Filament\Resources\Cinemas\CinemaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCinemas extends ListRecords
{
    protected static string $resource = CinemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
