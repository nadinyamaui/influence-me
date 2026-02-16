<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalApproved extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Proposal $proposal,
    ) {
        $this->proposal->loadMissing(['client', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proposal Approved: '.$this->proposal->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.proposal-approved',
            with: [
                'proposalTitle' => $this->proposal->title,
                'clientName' => $this->proposal->client?->name ?? 'Your client',
                'proposalUrl' => route('proposals.show', $this->proposal),
            ],
        );
    }
}
