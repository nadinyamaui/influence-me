<?php

namespace App\Livewire\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);

        $this->proposal = $proposal->load([
            'client:id,name',
            'campaigns' => fn ($query) => $query->orderBy('name'),
            'campaigns.scheduledPosts' => fn ($query) => $query
                ->with('instagramAccount:id,username')
                ->orderBy('scheduled_at'),
        ]);
    }

    public function statusLabel(): string
    {
        return match ($this->proposal->status) {
            ProposalStatus::Draft => 'Draft',
            ProposalStatus::Sent => 'Sent',
            ProposalStatus::Approved => 'Approved',
            ProposalStatus::Rejected => 'Rejected',
            ProposalStatus::Revised => 'Revised',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->proposal->status) {
            ProposalStatus::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
            ProposalStatus::Sent => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
            ProposalStatus::Approved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
            ProposalStatus::Rejected => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
            ProposalStatus::Revised => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
        };
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
