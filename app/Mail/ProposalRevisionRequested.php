<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalRevisionRequested extends Mailable
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
            subject: 'Revision Requested: '.$this->proposal->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.proposal-revision-requested',
            with: [
                'proposalTitle' => $this->proposal->title,
                'clientName' => $this->proposal->client?->name ?? 'Your client',
                'revisionNotes' => $this->proposal->revision_notes,
                'editUrl' => route('proposals.edit', $this->proposal),
            ],
        );
    }
}
