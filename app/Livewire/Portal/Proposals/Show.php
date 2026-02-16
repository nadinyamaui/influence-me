<?php

namespace App\Livewire\Portal\Proposals;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Proposal;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public bool $requestingChanges = false;

    public string $revisionNotes = '';

    public function mount(Proposal $proposal): void
    {
        $client = $this->authenticatedClient();

        $this->proposal = $client->proposals()
            ->whereKey($proposal->id)
            ->whereIn('status', $this->viewableStatuses())
            ->with([
                'user:id,name,email',
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

    public function approve(ProposalWorkflowService $proposalWorkflowService): void
    {
        try {
            $this->proposal = $proposalWorkflowService->approve($this->authenticatedClientUser(), $this->proposal)->load([
                'user:id,name,email',
                'campaigns' => fn ($query) => $query->orderBy('name'),
                'campaigns.scheduledPosts' => fn ($query) => $query
                    ->with('instagramAccount:id,username')
                    ->orderBy('scheduled_at'),
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->requestingChanges = false;
        $this->revisionNotes = '';
        $this->resetErrorBag();
        session()->flash('status', 'Proposal approved!');
    }

    public function openRequestChanges(): void
    {
        if (! $this->proposal->canRespond()) {
            $this->addError('proposal', 'This proposal has already been responded to.');

            return;
        }

        $this->requestingChanges = true;
    }

    public function cancelRequestChanges(): void
    {
        $this->requestingChanges = false;
        $this->revisionNotes = '';
        $this->resetErrorBag('revisionNotes');
    }

    public function requestChanges(ProposalWorkflowService $proposalWorkflowService): void
    {
        if (! $this->proposal->canRespond()) {
            $this->addError('proposal', 'This proposal has already been responded to.');

            return;
        }

        $this->validate([
            'revisionNotes' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->proposal = $proposalWorkflowService->requestChanges(
                $this->authenticatedClientUser(),
                $this->proposal,
                $this->revisionNotes,
            )->load([
                'user:id,name,email',
                'campaigns' => fn ($query) => $query->orderBy('name'),
                'campaigns.scheduledPosts' => fn ($query) => $query
                    ->with('instagramAccount:id,username')
                    ->orderBy('scheduled_at'),
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->requestingChanges = false;
        $this->revisionNotes = '';
        $this->resetErrorBag();
        session()->flash('status', 'Revision request sent.');
    }

    private function authenticatedClient(): Client
    {
        $client = Auth::guard('client')->user()?->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        return $client;
    }

    private function authenticatedClientUser(): ClientUser
    {
        $clientUser = Auth::guard('client')->user();

        if (! $clientUser instanceof ClientUser) {
            abort(403);
        }

        return $clientUser;
    }

    private function viewableStatuses(): array
    {
        return [
            ProposalStatus::Sent->value,
            ProposalStatus::Approved->value,
            ProposalStatus::Rejected->value,
            ProposalStatus::Revised->value,
        ];
    }
}
