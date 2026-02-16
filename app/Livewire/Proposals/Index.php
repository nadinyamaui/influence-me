<?php

namespace App\Livewire\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $status = 'all';

    public string $client = 'all';

    public function mount(): void
    {
        $this->authorize('viewAny', Proposal::class);
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, array_merge(['all'], $this->filterStatuses()), true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    public function updatedClient(): void
    {
        $this->resetPage();
    }

    public function delete(int $proposalId): void
    {
        $proposal = User::resolveProposal($proposalId);
        $this->authorize('delete', $proposal);

        $this->resetErrorBag('delete');

        $proposal->delete();

        session()->flash('status', 'Proposal deleted.');
    }

    public function render()
    {
        return view('pages.proposals.index', [
            'proposals' => $this->proposals(),
            'clients' => User::availableClients(),
        ])->layout('layouts.app', [
            'title' => __('Proposals'),
        ]);
    }

    private function proposals(): LengthAwarePaginator
    {
        $scheduledContentSubquery = ScheduledPost::query()
            ->selectRaw('count(*)')
            ->whereIn('campaign_id', function ($query): void {
                $query->select('id')
                    ->from('campaigns')
                    ->whereColumn('campaigns.proposal_id', 'proposals.id');
            });

        $query = Auth::user()->proposals()
            ->with('client')
            ->withCount('campaigns')
            ->addSelect(['scheduled_content_count' => $scheduledContentSubquery])
            ->latest();

        if (in_array($this->status, $this->filterStatuses(), true)) {
            $query->where('status', $this->status);
        }

        if ($this->client !== 'all' && is_numeric($this->client)) {
            $query->where('client_id', (int) $this->client);
        }

        return $query->paginate(10);
    }

    private function filterStatuses(): array
    {
        return array_map(
            fn (ProposalStatus $status): string => $status->value,
            ProposalStatus::cases(),
        );
    }
}
