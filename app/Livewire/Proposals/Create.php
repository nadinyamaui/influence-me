<?php

namespace App\Livewire\Proposals;

use App\Livewire\Forms\ProposalForm;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public ProposalForm $form;

    public bool $previewing = false;

    public function mount(): void
    {
        $this->authorize('create', Proposal::class);

        $this->form->addCampaign();
    }

    public function togglePreview(): void
    {
        $this->previewing = ! $this->previewing;
    }

    public function addCampaign(): void
    {
        $this->form->addCampaign();
    }

    public function removeCampaign(int $index): void
    {
        $this->form->removeCampaign($index);
    }

    public function addScheduledItem(int $campaignIndex): void
    {
        $this->form->addScheduledItem($campaignIndex);
    }

    public function removeScheduledItem(int $campaignIndex, int $itemIndex): void
    {
        $this->form->removeScheduledItem($campaignIndex, $itemIndex);
    }

    public function save()
    {
        $this->authorize('create', Proposal::class);

        $this->form->validate();

        ProposalWorkflowService::createDraftWithCampaignSchedule(
            auth()->user(),
            [
                'client_id' => $this->form->client_id,
                'title' => $this->form->title,
                'content' => $this->form->content,
                'campaigns' => $this->form->campaigns,
            ],
        );

        session()->flash('status', 'Proposal created.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function render()
    {
        return view('pages.proposals.create', [
            'clients' => User::availableClients(),
            'accounts' => User::accounts(),
        ])->layout('layouts.app', [
            'title' => __('New Proposal'),
        ]);
    }
}
