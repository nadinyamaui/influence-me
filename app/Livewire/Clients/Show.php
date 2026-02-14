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

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);

        $this->client = $client;
    }

    public function render()
    {
        $linkedContentMedia = $this->linkedContentMedia();

        return view('pages.clients.show', [
            'summary' => $this->summary(),
            'linkedContentGroups' => $this->groupedLinkedContent($linkedContentMedia),
            'linkedContentSummary' => $this->linkedContentSummary($linkedContentMedia),
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

        session()->flash('status', 'Content unlinked from client.');
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
