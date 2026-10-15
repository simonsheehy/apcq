<?php

namespace App\Filament\Resources\TrainingFaqs\Pages;

use App\Filament\Resources\TrainingFaqs\TrainingFaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingFaq extends CreateRecord
{
    protected static string $resource = TrainingFaqResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) static::getModel()::max('sort_order') + 1;

        return $data;
    }
}
