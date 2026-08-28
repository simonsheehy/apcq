<?php

namespace App\Mail;

use App\Models\Cinema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CinemaValidationRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public function __construct(public Cinema $cinema) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Collecte d’information - Remplacement des projecteurs - Subvention SODEC',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cinema-validation-request',
        );
    }
}
