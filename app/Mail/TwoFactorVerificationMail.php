<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TwoFactorVerificationMail extends Mailable
{
    public $code;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($code,User $user)
    {
        $this->code = $code;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.labels.two_factor_otp_email_subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email-template.two-factor-otp-email',
        );
    }
}
