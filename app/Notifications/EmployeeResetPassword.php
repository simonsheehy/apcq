<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(Employee $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Employee $notifiable): MailMessage
    {
        $url = route('portal.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line('Vous recevez ce courriel parce qu’une réinitialisation du mot de passe a été demandée pour votre compte du Centre d’expertise.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expirera dans '.config('auth.passwords.employees.expire').' minutes.')
            ->line('Si vous n’avez pas fait cette demande, vous pouvez ignorer ce courriel.')
            ->salutation("Au plaisir,\nL’équipe de l’APCQ");
    }
}
