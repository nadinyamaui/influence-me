<?php

namespace App\Livewire\Portal\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    public Proposal $proposal;

    public bool $showRequestChangesModal = false;

    public string $revisionNotes = '';

    public function mount(Proposal $proposal): void
    {
        $client = $this->authenticatedClient();

        $this->proposal = $this->resolveClientProposal($client, $proposal->id);
    }

    public function approve(ProposalWorkflowService $proposalWorkflowService): void
    {
        try {
            $this->proposal = $proposalWorkflowService
                ->approveByClient($this->authenticatedClient(), $this->proposal)
                ->load([
                    'campaigns' => fn ($query) => $query->orderBy('name'),
                    'campaigns.scheduledPosts' => fn ($query) => $query
                        ->with('instagramAccount:id,username')
                        ->orderBy('scheduled_at'),
                ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->resetErrorBag();
        $this->showRequestChangesModal = false;
        $this->revisionNotes = '';

        session()->flash('status', 'Proposal approved!');
    }

    public function openRequestChangesModal(): void
    {
        if (! $this->canRespond()) {
            $this->addError('response', 'Only sent proposals can be responded to.');

            return;
        }

        $this->showRequestChangesModal = true;
    }

    public function closeRequestChangesModal(): void
    {
        $this->showRequestChangesModal = false;
        $this->revisionNotes = '';
        $this->resetErrorBag(['revisionNotes']);
    }

    public function requestChanges(ProposalWorkflowService $proposalWorkflowService): void
    {
        $this->validate([
            'revisionNotes' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        try {
            $this->proposal = $proposalWorkflowService
                ->requestChangesByClient($this->authenticatedClient(), $this->proposal, $this->revisionNotes)
                ->load([
                    'campaigns' => fn ($query) => $query->orderBy('name'),
                    'campaigns.scheduledPosts' => fn ($query) => $query
                        ->with('instagramAccount:id,username')
                        ->orderBy('scheduled_at'),
                ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->closeRequestChangesModal();
        $this->resetErrorBag();

        session()->flash('status', 'Revision request sent.');
    }

    public function totalScheduledItems(): int
    {
        return (int) $this->proposal->campaigns
            ->sum(fn ($campaign): int => $campaign->scheduledPosts->count());
    }

    public function render()
    {
        return view('pages.portal.proposals.show')->layout('layouts.portal', [
            'title' => $this->proposal->title,
        ]);
    }

    private function canRespond(): bool
    {
        return $this->proposal->status === ProposalStatus::Sent
            && $this->proposal->responded_at === null;
    }

    private function authenticatedClient(): Client
    {
        $client = Auth::guard('client')->user()?->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        return $client;
    }

    private function resolveClientProposal(Client $client, int $proposalId): Proposal
    {
        return $client->proposals()
            ->whereKey($proposalId)
            ->whereIn('status', ProposalStatus::clientViewableValues())
            ->with([
                'campaigns' => fn ($query) => $query->orderBy('name'),
                'campaigns.scheduledPosts' => fn ($query) => $query
                    ->with('instagramAccount:id,username')
                    ->orderBy('scheduled_at'),
            ])
            ->firstOrFail();
    }
}
