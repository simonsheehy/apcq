<?php

namespace App\Filament\Resources\Cinemas\Pages;

use App\Filament\Resources\Cinemas\CinemaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCinema extends ViewRecord
{
    protected static string $resource = CinemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
