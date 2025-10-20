<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hola ' . $this->username . ', tu cuenta en Haciendas Family & Fitness Club (App para reserva de canchas) ha sido eliminada.',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_deleted',
            with: ['username' => $this->username]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
