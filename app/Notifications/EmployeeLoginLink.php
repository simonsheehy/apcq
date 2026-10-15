<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeLoginLink extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $loginUrl) {}

    /**
     * @return array<int, string>
     */
    public function via(Employee $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Employee $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre lien de connexion au Centre d’expertise')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line('Voici votre lien pour accéder au Centre d’expertise de l’APCQ :')
            ->action('Me connecter', $this->loginUrl)
            ->line('Ce lien ne fonctionne qu’une seule fois et expire dans '.config('trainings.login_link_expires_in_minutes').' minutes.')
            ->line('Si vous n’avez pas fait cette demande, vous pouvez ignorer ce courriel.')
            ->salutation("Au plaisir,\nL’équipe de l’APCQ");
    }
}
