<?php

namespace Control\Auth\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetEmail extends Mailable
{
    public function envelope()
    {
        return new Envelope(
            subject: 'Du har endret passordet ditt',
        );
    }

    public function content()
    {
        return new Content(
            view: 'mail.reset-password-email',
        );
    }
}
