<?php

namespace App\Livewire\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
        if (! in_array($this->status, array_merge(['all'], $this->filterStatuses()), true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    public function updatedClientId(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $proposalId): void
    {
        $proposal = User::resolveProposal($proposalId);
        $this->authorize('delete', $proposal);

        $this->resetErrorBag('delete');
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

        $proposal = User::resolveProposal($this->deletingProposalId);
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
            ->with(['client', 'campaigns.scheduledPosts'])
            ->withCount('campaigns')
            ->orderByDesc('updated_at');

        if (in_array($this->status, $this->filterStatuses(), true)) {
            $query->where('status', $this->status);
        }

        if ($this->clientId !== 'all' && is_numeric($this->clientId)) {
            $query->where('client_id', (int) $this->clientId);
        }

        return $query->paginate(10);
    }

    private function clients(): array
    {
        return Auth::user()->clients()
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    private function filterStatuses(): array
    {
        return [
            ProposalStatus::Draft->value,
            ProposalStatus::Sent->value,
            ProposalStatus::Approved->value,
            ProposalStatus::Rejected->value,
            ProposalStatus::Revised->value,
        ];
    }
}
