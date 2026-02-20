<?php

namespace App\Livewire\Portal;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\SocialAccountMedia;
use Illuminate\Database\Eloquent\Builder;
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

        $pendingInvoiceQuery = $client->invoices()->whereIn('status', InvoiceStatus::pendingValues());

        $linkedMediaQuery = SocialAccountMedia::query()
            ->whereHas('campaigns', fn (Builder $builder): Builder => $builder->where('campaigns.client_id', $client->id));

        return view('pages.portal.dashboard', [
            'client' => $client,
            'influencerName' => $client->user?->name,
            'summary' => [
                'active_proposals' => $client->proposals()->where('status', ProposalStatus::Sent)->count(),
                'pending_invoice_count' => $pendingInvoiceQuery->count(),
                'pending_invoice_total' => (float) $pendingInvoiceQuery->sum('total'),
                'linked_content_count' => (clone $linkedMediaQuery)->count(),
                'total_reach' => (int) (clone $linkedMediaQuery)->sum('reach'),
            ],
            'recentProposals' => $client->proposals()->latest()->take(5)->get(),
            'recentInvoices' => $client->invoices()->latest()->take(5)->get(),
        ])->layout('layouts.portal', [
            'title' => __('Dashboard'),
        ]);
    }
}
