<?php

namespace App\Filament\Actions;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;
use Livewire\Component;

class CopyEmployeeLoginLinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copyLoginLink';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Copier le lien de connexion')
            ->icon(Heroicon::OutlinedLink)
            ->color('gray')
            ->tooltip('Génère un nouveau lien personnel à usage unique. Il remplace le lien envoyé précédemment.')
            ->visible(fn (?Employee $record): bool => $record instanceof Employee && $this->canHaveLoginLink($record))
            ->action(function (Component $livewire, ?Employee $record = null): void {
                $employee = $record;

                if (! $employee instanceof Employee && method_exists($livewire, 'getRecord')) {
                    $employee = $livewire->getRecord();
                }

                if (! $employee instanceof Employee || ! $this->canHaveLoginLink($employee)) {
                    return;
                }

                $url = $employee->loginUrlForAdministrator();

                $livewire->js('navigator.clipboard.writeText('.Js::from($url).')');

                Notification::make()
                    ->title('Lien de connexion copié dans le presse-papiers')
                    ->body($employee->hasAcceptedInvitation()
                        ? 'Valide '.config('trainings.administrator_login_link_expires_in_hours').' heures, pour une seule connexion. Transmettez-le uniquement à '.$employee->full_name.'.'
                        : 'Lien d’invitation, valide '.config('trainings.invitation_expires_in_days').' jours. Transmettez-le uniquement à '.$employee->full_name.'.')
                    ->success()
                    ->send();
            });
    }

    private function canHaveLoginLink(Employee $employee): bool
    {
        return match ($employee->status) {
            EmployeeStatus::Active => $employee->canAccessCinema(),
            EmployeeStatus::Invited => $employee->invited_at !== null && $employee->canAccessCinema(),
            EmployeeStatus::Deactivated => false,
        };
    }
}
