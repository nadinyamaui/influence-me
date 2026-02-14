<?php

namespace App\Livewire\Clients;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Services\Clients\ClientPortalAccessService;
use App\Services\Content\ContentClientLinkService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public string $activeTab = 'overview';

    public bool $confirmingRevokePortalAccess = false;

    private ?Collection $linkedContentGroups = null;

    public array $linkedContentSummaryData = [];

    private bool $linkedContentLoaded = false;

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);

        $this->client = $client;
        $this->linkedContentGroups = collect();
        $this->linkedContentSummaryData = $this->emptyLinkedContentSummary();
    }

    public function render()
    {
        return view('pages.clients.show', [
            'summary' => $this->summary(),
            'linkedContentGroups' => $this->linkedContentGroups ?? collect(),
            'linkedContentSummary' => $this->linkedContentSummaryData,
            'hasPortalAccess' => $this->client->clientUser()->exists(),
        ])->layout('layouts.app', [
            'title' => __('Client Details'),
        ]);
    }

    public function updatedActiveTab(string $value): void
    {
        if ($value === 'content') {
            $this->loadLinkedContent();
        }
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
        session()->flash('status', 'Portal invitation sent successfully.');
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
        session()->flash('status', 'Portal access revoked.');
    }

    public function unlinkContent(int $mediaId, ContentClientLinkService $linkService): void
    {
        $this->authorize('update', $this->client);

        $media = $this->client->instagramMedia()
            ->whereKey($mediaId)
            ->firstOrFail();

        $this->authorize('linkToClient', $media);

        $linkService->unlink(Auth::user(), $media, $this->client);

        if ($this->activeTab === 'content') {
            $this->linkedContentLoaded = false;
            $this->loadLinkedContent();
        }

        session()->flash('status', 'Content unlinked from client.');
    }

    private function loadLinkedContent(): void
    {
        if ($this->linkedContentLoaded) {
            return;
        }

        $linkedContentMedia = $this->linkedContentMedia();

        $this->linkedContentGroups = $this->groupedLinkedContent($linkedContentMedia);
        $this->linkedContentSummaryData = $this->linkedContentSummary($linkedContentMedia);
        $this->linkedContentLoaded = true;
    }

    private function emptyLinkedContentSummary(): array
    {
        return [
            'total_posts' => 0,
            'total_reach' => 0,
            'total_impressions' => 0,
            'average_engagement_rate' => 0.0,
        ];
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

    private function linkedContentMedia(): EloquentCollection
    {
        return $this->client->instagramMedia()
            ->orderByRaw('case when campaign_media.campaign_name is null then 1 else 0 end')
            ->orderBy('campaign_media.campaign_name')
            ->orderByDesc('published_at')
            ->get();
    }

    private function groupedLinkedContent(EloquentCollection $linkedContentMedia): Collection
    {
        return $linkedContentMedia->groupBy(function ($media): string {
            return $media->pivot->campaign_name ?? 'Uncategorized';
        });
    }

    private function linkedContentSummary(EloquentCollection $linkedContentMedia): array
    {
        $averageEngagementRate = $linkedContentMedia->isEmpty()
            ? 0
            : (float) $linkedContentMedia->avg(fn ($media): float => (float) $media->engagement_rate);

        return [
            'total_posts' => $linkedContentMedia->count(),
            'total_reach' => (int) $linkedContentMedia->sum('reach'),
            'total_impressions' => (int) $linkedContentMedia->sum('impressions'),
            'average_engagement_rate' => round($averageEngagementRate, 2),
        ];
    }
}
