<?php

namespace App\Livewire\Portal\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $client = $this->authenticatedClient();

        $this->proposal = $client->proposals()
            ->whereKey($proposal->id)
            ->whereIn('status', ProposalStatus::clientViewableValues())
            ->with([
                'campaigns' => fn ($query) => $query->orderBy('name'),
                'campaigns.scheduledPosts' => fn ($query) => $query
                    ->with('instagramAccount:id,username')
                    ->orderBy('scheduled_at'),
            ])
            ->firstOrFail();
    }

    public function statusLabel(): string
    {
        return match ($this->proposal->status) {
            ProposalStatus::Sent => 'Sent',
            ProposalStatus::Approved => 'Approved',
            ProposalStatus::Rejected => 'Rejected',
            ProposalStatus::Revised => 'Revised',
            ProposalStatus::Draft => 'Draft',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->proposal->status) {
            ProposalStatus::Sent => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
            ProposalStatus::Approved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
            ProposalStatus::Rejected => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
            ProposalStatus::Revised => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
            ProposalStatus::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
        };
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

    private function authenticatedClient(): Client
    {
        $client = Auth::guard('client')->user()?->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        return $client;
    }
}
