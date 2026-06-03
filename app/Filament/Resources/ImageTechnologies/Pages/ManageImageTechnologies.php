<?php

namespace App\Filament\Resources\ImageTechnologies\Pages;

use App\Filament\Resources\ImageTechnologies\ImageTechnologyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageImageTechnologies extends ManageRecords
{
    protected static string $resource = ImageTechnologyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
