<?php

namespace App\Filament\Resources\TrainingFaqs\Pages;

use App\Filament\Resources\TrainingFaqs\TrainingFaqResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingFaq extends EditRecord
{
    protected static string $resource = TrainingFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
