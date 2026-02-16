<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
            view: 'mail.proposal-revision-requested',
            with: [
                'clientName' => $this->proposal->client?->name ?? 'Your client',
                'proposalTitle' => $this->proposal->title,
                'revisionNotes' => $this->proposal->revision_notes,
                'editProposalUrl' => route('proposals.edit', $this->proposal),
            ],
        );
    }
}
