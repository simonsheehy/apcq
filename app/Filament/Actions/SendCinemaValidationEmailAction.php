<?php

namespace App\Filament\Actions;

use App\Models\Cinema;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class SendCinemaValidationEmailAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendValidationEmail';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Envoyer la demande de saisie')
            ->icon(Heroicon::OutlinedEnvelope)
            ->requiresConfirmation()
            ->modalHeading(fn (?Cinema $record): string => $record instanceof Cinema
                ? 'Envoyer la demande de saisie — '.$record->name
                : 'Envoyer la demande de saisie')
            ->modalDescription(function (?Cinema $record): string {
                if (! $record instanceof Cinema) {
                    return 'Un courriel contenant le lien unique du formulaire sera envoyé au contact du cinéma.';
                }

                $recipients = $record->validationRequestRecipients();

                if ($recipients === []) {
                    return 'Aucun courriel de contact n’est défini pour ce cinéma.';
                }

                return 'Un courriel contenant le lien unique du formulaire sera envoyé à : '.implode(', ', $recipients).'.';
            })
            ->modalSubmitActionLabel('Envoyer')
            ->disabled(fn (?Cinema $record): bool => $record instanceof Cinema && $record->validationRequestRecipients() === [])
            ->tooltip(function (?Cinema $record): ?string {
                if ($record instanceof Cinema && $record->validationRequestRecipients() === []) {
                    return 'Aucun courriel de contact n’est défini.';
                }

                return null;
            })
            ->successNotificationTitle('Courriel envoyé')
            ->failureNotificationTitle('Impossible d’envoyer le courriel')
            ->action(function (Cinema $record): void {
                if (! $record->sendValidationRequest()) {
                    $this->failure();
                }
            });
    }
}
