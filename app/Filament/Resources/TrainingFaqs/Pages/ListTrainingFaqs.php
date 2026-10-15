<?php

namespace App\Filament\Resources\TrainingFaqs\Pages;

use App\Filament\Resources\TrainingFaqs\TrainingFaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingFaqs extends ListRecords
{
    protected static string $resource = TrainingFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
