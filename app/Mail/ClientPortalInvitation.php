<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPortalInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $influencerName,
        public string $temporaryPassword,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to the Influence Me client portal",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client-portal-invitation',
        );
    }
}
