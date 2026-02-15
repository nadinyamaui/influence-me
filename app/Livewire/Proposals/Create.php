<?php

namespace App\Livewire\Proposals;

use App\Http\Requests\StoreProposalRequest;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public string $title = '';

    public string $client_id = '';

    public function mount(): void
    {
        $this->authorize('create', Proposal::class);
    }

    protected function rules(): array
    {
        return StoreProposalRequest::initialRulesFor((int) auth()->id());
    }

    public function save(ProposalWorkflowService $proposalWorkflowService)
    {
        $this->authorize('create', Proposal::class);

        $validated = $this->validate();

        $proposal = $proposalWorkflowService->createDraft(auth()->user(), $validated);

        session()->flash('status', 'Proposal created. Continue building details.');

        return $this->redirectRoute('proposals.edit', ['proposal' => $proposal->id], navigate: true);
    }

    public function render()
    {
        return view('pages.proposals.create', [
            'clients' => User::availableClients(),
        ])->layout('layouts.app', [
            'title' => __('Create Proposal'),
        ]);
    }
}
