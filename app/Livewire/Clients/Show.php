<?php

namespace App\Livewire\Clients;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Services\Clients\ClientPortalAccessService;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public string $activeTab = 'overview';

    public bool $confirmingRevokePortalAccess = false;

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);

        $this->client = $client;
    }

    public function render()
    {
        return view('pages.clients.show', [
            'summary' => $this->summary(),
            'hasPortalAccess' => $this->client->clientUser()->exists(),
        ])->layout('layouts.app', [
            'title' => __('Client Details'),
        ]);
    }

    public function inviteToPortal(ClientPortalAccessService $portalAccessService): void
    {
        $this->authorize('update', $this->client);

        $influencerName = Auth::user()?->name ?? 'Your influencer';

        try {
            $portalAccessService->invite($this->client, $influencerName);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        $this->resetErrorBag();
        Flux::toast('Portal invitation sent successfully.', variant: 'success');
    }

    public function confirmRevokePortalAccess(): void
    {
        $this->authorize('update', $this->client);

        if (! $this->client->clientUser()->exists()) {
            $this->addError('revoke', 'Portal access is not active for this client.');

            return;
        }

        $this->resetErrorBag('revoke');
        $this->confirmingRevokePortalAccess = true;
    }

    public function cancelRevokePortalAccess(): void
    {
        $this->confirmingRevokePortalAccess = false;
    }

    public function revokePortalAccess(ClientPortalAccessService $portalAccessService): void
    {
        $this->authorize('update', $this->client);

        try {
            $portalAccessService->revoke($this->client);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            $this->confirmingRevokePortalAccess = false;

            return;
        }

        $this->confirmingRevokePortalAccess = false;
        $this->resetErrorBag();
        Flux::toast('Portal access revoked.', variant: 'success');
    }

    private function summary(): array
    {
        $pendingInvoiceQuery = $this->client->invoices()
            ->whereIn('status', [
                InvoiceStatus::Sent,
                InvoiceStatus::Overdue,
            ]);

        return [
            'linked_posts' => $this->client->instagramMedia()->count(),
            'active_proposals' => $this->client->proposals()->where('status', ProposalStatus::Sent)->count(),
            'pending_invoices' => $pendingInvoiceQuery->count(),
            'pending_invoice_total' => (float) $pendingInvoiceQuery->sum('total'),
        ];
    }
}
