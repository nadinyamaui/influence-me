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
