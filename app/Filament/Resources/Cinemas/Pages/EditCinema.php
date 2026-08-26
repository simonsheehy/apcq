<?php

namespace App\Filament\Resources\Cinemas\Pages;

use App\Filament\Actions\CopyCinemaValidationLinkAction;
use App\Filament\Actions\SendCinemaValidationEmailAction;
use App\Filament\Resources\Cinemas\CinemaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCinema extends EditRecord
{
    protected static string $resource = CinemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendCinemaValidationEmailAction::make(),
            Action::make('openValidationLink')
                ->label('Ouvrir le formulaire')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $this->getRecord()->validationUrl())
                ->openUrlInNewTab(),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
