<?php

namespace App\Filament\Resources\AdministrativeRegions\Pages;

use App\Filament\Resources\AdministrativeRegions\AdministrativeRegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAdministrativeRegions extends ManageRecords
{
    protected static string $resource = AdministrativeRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
