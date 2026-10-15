<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(Employee $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Employee $notifiable): MailMessage
    {
        $cinemaName = $notifiable->cinema()->withTrashed()->value('name');

        return (new MailMessage)
            ->subject('Invitation au Centre d’expertise de l’APCQ')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Vous êtes invité·e à accéder au Centre d’expertise de l’APCQ, la plateforme de formation des employés de {$cinemaName}.")
            ->line('Cliquez sur le bouton ci-dessous pour accéder directement à vos formations :')
            ->action('Accéder au Centre d’expertise', $notifiable->invitationUrl())
            ->line('Ce lien est personnel : ne le transférez pas. Il fonctionne une seule fois et reste valide pendant '.config('trainings.invitation_expires_in_days').' jours.')
            ->line('Pour vos prochaines visites, demandez simplement un lien de connexion avec votre adresse courriel sur '.route('portal.login').'.')
            ->salutation("Au plaisir,\nL’équipe de l’APCQ");
    }
}
