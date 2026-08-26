<?php

namespace App\Filament\Actions;

use App\Models\Cinema;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Number;
use Throwable;

class SendCinemaValidationEmailBulkAction extends BulkAction
{
    use CanCustomizeProcess;

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
            ->modalHeading('Envoyer la demande de saisie')
            ->modalDescription('Chaque cinéma sélectionné recevra un courriel contenant son lien unique vers le formulaire de validation.')
            ->modalSubmitActionLabel('Envoyer')
            ->successNotificationTitle(function (): string {
                if ($this->successfulSelectedRecordsCount === 1) {
                    return 'Courriel envoyé';
                }

                return Number::format($this->successfulSelectedRecordsCount).' courriels envoyés';
            })
            ->failureNotificationTitle(function (int $successCount, int $totalCount): string {
                if ($successCount) {
                    return Number::format($successCount).' courriel(s) envoyé(s) sur '.Number::format($totalCount);
                }

                return 'Aucun courriel envoyé';
            })
            ->action(function (): void {
                $this->process(static function (SendCinemaValidationEmailBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                    $isFirstException = true;

                    $records->each(static function (Cinema $cinema) use ($action, &$isFirstException): void {
                        try {
                            if (! $cinema->sendValidationRequest()) {
                                $action->reportBulkProcessingFailure(
                                    'missing-email',
                                    fn (int $failureCount): string => $failureCount === 1
                                        ? '1 cinéma n’a pas d’adresse courriel.'
                                        : Number::format($failureCount).' cinémas n’ont pas d’adresse courriel.',
                                );
                            }
                        } catch (Throwable $exception) {
                            $action->reportBulkProcessingFailure();

                            if ($isFirstException) {
                                report($exception);
                                $isFirstException = false;
                            }
                        }
                    });
                });
            })
            ->deselectRecordsAfterCompletion();
    }
}
