<?php

namespace App\Livewire\Proposals;

use App\Enums\ProposalStatus;
use App\Livewire\Forms\ProposalForm;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public ProposalForm $form;

    public bool $previewing = false;

    public bool $confirmingDelete = false;

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);

        $this->proposal = $proposal;
        $this->form->setProposal($proposal);
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
        $this->authorize('update', $this->proposal);

        if (! $this->isEditable()) {
            abort(403);
        }

        $this->form->validate();

        ProposalWorkflowService::updateDraftWithCampaignSchedule(
            $this->proposal,
            [
                'client_id' => $this->form->client_id,
                'title' => $this->form->title,
                'content' => $this->form->content,
                'campaigns' => $this->form->campaigns,
            ],
        );

        session()->flash('status', 'Proposal updated.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->proposal);

        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function delete()
    {
        $this->authorize('delete', $this->proposal);

        $this->proposal->delete();

        $this->confirmingDelete = false;
        session()->flash('status', 'Proposal deleted.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function duplicate()
    {
        $this->authorize('create', Proposal::class);

        $campaigns = array_map(function (array $campaign): array {
            $campaign['name'] = $campaign['name'].' (Copy)';

            return $campaign;
        }, $this->form->campaigns);

        $newProposal = ProposalWorkflowService::createDraftWithCampaignSchedule(
            auth()->user(),
            [
                'client_id' => $this->proposal->client_id,
                'title' => $this->proposal->title.' (Copy)',
                'content' => $this->proposal->content,
                'campaigns' => $campaigns,
            ],
        );

        session()->flash('status', 'Proposal duplicated.');

        return $this->redirectRoute('proposals.edit', ['proposal' => $newProposal->id], navigate: true);
    }

    public function render()
    {
        return view('pages.proposals.edit', [
            'clients' => User::availableClients(),
            'accounts' => User::accounts(),
            'editable' => $this->isEditable(),
        ])->layout('layouts.app', [
            'title' => __('Edit Proposal'),
        ]);
    }

    private function isEditable(): bool
    {
        return in_array($this->proposal->status, [ProposalStatus::Draft, ProposalStatus::Revised], true);
    }
}
