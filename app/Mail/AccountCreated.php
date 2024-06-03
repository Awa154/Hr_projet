<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Création de compte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        $content = 'Votre compte a été créé. Voici vos identifiants de connexion :' . PHP_EOL
                 . 'Nom d\'utilisateur: ' . $this->username . PHP_EOL
                 . 'Mot de passe: ' . $this->password . PHP_EOL
                 . 'Veuillez garder cette information en sécurité et ne pas la partager avec quiconque.' . PHP_EOL
                 . 'Merci !';

        return $this->subject('Votre compte a été créé')
                    ->text($content);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
