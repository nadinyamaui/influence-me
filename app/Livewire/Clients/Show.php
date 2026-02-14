<?php

namespace App\Livewire\Clients;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public string $activeTab = 'overview';

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
