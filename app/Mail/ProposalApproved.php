<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
            view: 'mail.proposal-approved',
            with: [
                'clientName' => $this->proposal->client?->name ?? 'Your client',
                'proposalTitle' => $this->proposal->title,
                'proposalUrl' => route('proposals.show', $this->proposal),
            ],
        );
    }
}
