<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProposalSent extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Proposal $proposal,
    ) {
        $this->proposal->loadMissing(['user', 'client.clientUser']);
    }

    public function envelope(): Envelope
    {
        $replyTo = [];

        if (filled($this->proposal->user?->email)) {
            $replyTo = [
                new Address(
                    $this->proposal->user->email,
                    $this->proposal->user?->name,
                ),
            ];
        }

        return new Envelope(
            subject: 'New Proposal: '.$this->proposal->title,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.proposal-sent',
            with: [
                'influencerName' => $this->proposal->user?->name ?? 'Your influencer',
                'proposalTitle' => $this->proposal->title,
                'proposalPreview' => Str::limit(Str::of($this->proposal->content)->squish()->value(), 200),
                'hasPortalAccess' => $this->proposal->client?->clientUser !== null,
                'portalUrl' => route('portal.login'),
            ],
        );
    }
}
