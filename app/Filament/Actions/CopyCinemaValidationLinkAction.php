<?php

namespace App\Filament\Actions;

use App\Models\Cinema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;
use Livewire\Component;

class CopyCinemaValidationLinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copyValidationLink';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Copier le lien unique')
            ->icon(Heroicon::OutlinedClipboardDocument)
            ->color('gray')
            ->action(function (Component $livewire, ?Cinema $record = null): void {
                $cinema = $record;

                if (! $cinema instanceof Cinema && method_exists($livewire, 'getRecord')) {
                    $cinema = $livewire->getRecord();
                }

                if (! $cinema instanceof Cinema) {
                    return;
                }

                $livewire->js('navigator.clipboard.writeText('.Js::from($cinema->validationUrl()).')');

                Notification::make()
                    ->title('Lien copié dans le presse-papiers')
                    ->success()
                    ->send();
            });
    }
}
