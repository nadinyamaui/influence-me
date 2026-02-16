<?php

namespace App\Livewire\Portal\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\ScheduledPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public function updatedStatus(): void
    {
        if (! in_array($this->status, array_merge(['all'], $this->filterStatuses()), true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    public function render()
    {
        return view('pages.portal.proposals.index', [
            'proposals' => $this->proposals(),
            'filterStatuses' => $this->filterStatuses(),
        ])->layout('layouts.portal', [
            'title' => __('Proposals'),
        ]);
    }

    private function proposals(): LengthAwarePaginator
    {
        $client = $this->authenticatedClient();

        $scheduledContentSubquery = ScheduledPost::query()
            ->selectRaw('count(*)')
            ->whereIn('campaign_id', function ($query): void {
                $query->select('id')
                    ->from('campaigns')
                    ->whereColumn('campaigns.proposal_id', 'proposals.id');
            });

        $query = $client->proposals()
            ->whereIn('status', $this->filterStatuses())
            ->withCount('campaigns')
            ->addSelect(['scheduled_content_count' => $scheduledContentSubquery])
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        if (in_array($this->status, $this->filterStatuses(), true)) {
            $query->where('status', $this->status);
        }

        return $query->paginate(10);
    }

    private function authenticatedClient(): Client
    {
        $client = Auth::guard('client')->user()?->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        return $client;
    }

    private function filterStatuses(): array
    {
        return [
            ProposalStatus::Sent->value,
            ProposalStatus::Approved->value,
            ProposalStatus::Rejected->value,
            ProposalStatus::Revised->value,
        ];
    }
}
