<?php

namespace App\Livewire\Portal;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $clientUser = Auth::guard('client')->user();

        $client = $clientUser->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        $pendingInvoiceQuery = $client->invoices()->whereIn('status', [
            InvoiceStatus::Sent,
            InvoiceStatus::Overdue,
        ]);

        return view('pages.portal.dashboard', [
            'client' => $client,
            'influencerName' => $client->user?->name,
            'summary' => [
                'active_proposals' => $client->proposals()->where('status', ProposalStatus::Sent)->count(),
                'pending_invoice_count' => $pendingInvoiceQuery->count(),
                'pending_invoice_total' => (float) $pendingInvoiceQuery->sum('total'),
                'linked_content_count' => $client->instagramMedia()->count(),
                'total_reach' => (int) $client->instagramMedia()->sum('reach'),
            ],
            'recentProposals' => $client->proposals()->latest()->take(5)->get(),
            'recentInvoices' => $client->invoices()->latest()->take(5)->get(),
        ])->layout('layouts.portal', [
            'title' => __('Dashboard'),
        ]);
    }
}
