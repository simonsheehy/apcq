<?php

namespace App\Filament\Resources\SeatTypes\Pages;

use App\Filament\Resources\SeatTypes\SeatTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSeatTypes extends ManageRecords
{
    protected static string $resource = SeatTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
