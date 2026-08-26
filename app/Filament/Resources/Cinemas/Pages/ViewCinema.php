<?php

namespace App\Filament\Resources\Cinemas\Pages;

use App\Filament\Actions\CopyCinemaValidationLinkAction;
use App\Filament\Actions\SendCinemaValidationEmailAction;
use App\Filament\Resources\Cinemas\CinemaResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewCinema extends ViewRecord
{
    protected static string $resource = CinemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendCinemaValidationEmailAction::make(),
            CopyCinemaValidationLinkAction::make(),
            Action::make('openValidationLink')
                ->label('Ouvrir le formulaire')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $this->getRecord()->validationUrl())
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
