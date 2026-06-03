<?php

namespace App\Filament\Resources\SoundTechnologies\Pages;

use App\Filament\Resources\SoundTechnologies\SoundTechnologyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSoundTechnologies extends ManageRecords
{
    protected static string $resource = SoundTechnologyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
