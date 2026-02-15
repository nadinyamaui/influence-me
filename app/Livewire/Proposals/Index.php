<?php

namespace App\Livewire\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $status = 'all';

    public string $clientId = 'all';

    public ?int $deletingProposalId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Proposal::class);
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, array_merge(['all'], $this->availableStatuses()), true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    public function updatedClientId(): void
    {
        if ($this->validatedClientId() === null && $this->clientId !== 'all') {
            $this->clientId = 'all';
        }

        $this->resetPage();
    }

    public function confirmDelete(int $proposalId): void
    {
        $proposal = $this->resolveProposal($proposalId);
        $this->authorize('delete', $proposal);

        $this->deletingProposalId = $proposal->id;
    }

    public function cancelDelete(): void
    {
        $this->deletingProposalId = null;
    }

    public function delete(): void
    {
        if ($this->deletingProposalId === null) {
            return;
        }

        $proposal = $this->resolveProposal($this->deletingProposalId);
        $this->authorize('delete', $proposal);

        $proposal->delete();

        $this->deletingProposalId = null;
        session()->flash('status', 'Proposal deleted.');
    }

    public function render()
    {
        return view('pages.proposals.index', [
            'proposals' => $this->proposals(),
            'clients' => $this->clients(),
            'statuses' => ProposalStatus::cases(),
        ])->layout('layouts.app', [
            'title' => __('Proposals'),
        ]);
    }

    public function deletingProposal(): ?Proposal
    {
        if ($this->deletingProposalId === null) {
            return null;
        }

        return Auth::user()->proposals()
            ->whereKey($this->deletingProposalId)
            ->first();
    }

    private function proposals(): LengthAwarePaginator
    {
        $query = Auth::user()->proposals()
            ->with('client:id,name')
            ->withCount('campaigns')
            ->addSelect([
                'scheduled_content_count' => ScheduledPost::query()
                    ->selectRaw('count(*)')
                    ->join('campaigns', 'campaigns.id', '=', 'scheduled_posts.campaign_id')
                    ->whereColumn('campaigns.proposal_id', 'proposals.id'),
            ])
            ->latest('id');

        if (in_array($this->status, $this->availableStatuses(), true)) {
            $query->where('status', $this->status);
        }

        $clientId = $this->validatedClientId();
        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        return $query->paginate(10);
    }

    private function clients(): EloquentCollection
    {
        return Auth::user()->clients()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validatedClientId(): ?int
    {
        if ($this->clientId === 'all') {
            return null;
        }

        $clientId = filter_var($this->clientId, FILTER_VALIDATE_INT);
        if ($clientId === false) {
            return null;
        }

        $exists = Auth::user()->clients()
            ->whereKey($clientId)
            ->exists();

        return $exists ? $clientId : null;
    }

    private function availableStatuses(): array
    {
        return array_map(
            static fn (ProposalStatus $status): string => $status->value,
            ProposalStatus::cases(),
        );
    }

    private function resolveProposal(int $proposalId): Proposal
    {
        return Auth::user()->proposals()
            ->whereKey($proposalId)
            ->firstOrFail();
    }
}
