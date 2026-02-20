<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);

        $this->proposal = $proposal->load([
            'client:id,name,email',
            'campaigns' => fn ($query) => $query->orderBy('name'),
            'campaigns.scheduledPosts' => fn ($query) => $query
                ->with('socialAccount:id,username')
                ->orderBy('scheduled_at'),
        ]);
    }

    public function send(ProposalWorkflowService $proposalWorkflowService): void
    {
        $this->authorize('send', $this->proposal);

        try {
            $this->proposal = $proposalWorkflowService->send(auth()->user(), $this->proposal)->load([
                'client:id,name,email',
                'campaigns' => fn ($query) => $query->orderBy('name'),
                'campaigns.scheduledPosts' => fn ($query) => $query
                    ->with('socialAccount:id,username')
                    ->orderBy('scheduled_at'),
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->resetErrorBag();
        session()->flash('status', 'Proposal sent to '.$this->proposal->client->name.'.');
    }

    public function totalScheduledItems(): int
    {
        return (int) $this->proposal->campaigns
            ->sum(fn ($campaign): int => $campaign->scheduledPosts->count());
    }

    public function render()
    {
        return view('pages.proposals.show')->layout('layouts.app', [
            'title' => __('Proposal Preview'),
        ]);
    }
}
