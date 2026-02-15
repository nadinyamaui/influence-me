<?php

namespace App\Livewire\Clients;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Livewire\Forms\CampaignForm;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\Proposal;
use App\Services\Clients\ClientPortalAccessService;
use App\Services\Content\ContentClientLinkService;
use Illuminate\Database\Eloquent\Builder;
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

    public Collection $linkedContentGroups;

    public array $linkedContentSummaryData = [];

    public bool $linkedContentLoaded = false;

    public bool $showCampaignModal = false;

    public ?int $editingCampaignId = null;

    public ?int $confirmingDeleteCampaignId = null;

    public CampaignForm $campaignForm;

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);

        $this->client = $client;
        $this->linkedContentGroups = collect();
        $this->linkedContentSummaryData = $this->emptyLinkedContentSummary();
        $this->linkedContentLoaded = false;
    }

    public function render()
    {
        if ($this->activeTab === 'content' && ! $this->linkedContentLoaded) {
            $this->loadLinkedContent();
        }

        return view('pages.clients.show', [
            'summary' => $this->summary(),
            'linkedContentGroups' => $this->linkedContentGroups ?? collect(),
            'linkedContentSummary' => $this->linkedContentSummaryData,
            'campaigns' => $this->campaigns(),
            'campaignProposals' => $this->campaignProposals(),
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

        $media = InstagramMedia::query()
            ->whereKey($mediaId)
            ->whereHas('campaigns', fn (Builder $builder): Builder => $builder->where('campaigns.client_id', $this->client->id))
            ->firstOrFail();

        $this->authorize('linkToClient', $media);

        $linkService->unlink(Auth::user(), $media, $this->client);

        if ($this->activeTab === 'content') {
            $this->linkedContentLoaded = false;
            $this->loadLinkedContent();
        }

        session()->flash('status', 'Content unlinked from client.');
    }

    public function openCreateCampaignModal(): void
    {
        $this->authorize('update', $this->client);

        $this->editingCampaignId = null;
        $this->campaignForm->clear();
        $this->resetErrorBag(['campaignForm.name', 'campaignForm.description', 'campaignForm.proposalId']);
        $this->showCampaignModal = true;
    }

    public function openEditCampaignModal(int $campaignId): void
    {
        $campaign = $this->client->resolveClientCampaign($campaignId);
        $this->authorize('update', $campaign);

        $this->editingCampaignId = $campaign->id;
        $this->campaignForm->setCampaign($campaign);
        $this->resetErrorBag(['campaignForm.name', 'campaignForm.description', 'campaignForm.proposalId']);
        $this->showCampaignModal = true;
    }

    public function closeCampaignModal(): void
    {
        $this->showCampaignModal = false;
        $this->resetErrorBag(['campaignForm.name', 'campaignForm.description', 'campaignForm.proposalId']);
    }

    public function saveCampaign(): void
    {
        $this->authorize('update', $this->client);

        $campaign = null;

        if ($this->editingCampaignId !== null) {
            $campaign = $this->client->resolveClientCampaign($this->editingCampaignId);
            $this->authorize('update', $campaign);
        }

        $this->campaignForm->validateForClient(
            clientId: $this->client->id,
            userId: (int) Auth::id(),
            ignoreCampaignId: $this->editingCampaignId,
            includeProposal: true,
        );

        $payload = $this->campaignForm->payload(includeProposal: true);

        if ($campaign === null) {
            $this->client->campaigns()->create($payload);
            session()->flash('status', 'Campaign created.');
        } else {
            $campaign->update($payload);
            session()->flash('status', 'Campaign updated.');
        }

        $this->closeCampaignModal();
    }

    public function confirmDeleteCampaign(int $campaignId): void
    {
        $campaign = $this->client->resolveClientCampaign($campaignId);
        $this->authorize('delete', $campaign);

        $this->confirmingDeleteCampaignId = $campaign->id;
    }

    public function cancelDeleteCampaign(): void
    {
        $this->confirmingDeleteCampaignId = null;
    }

    public function deleteCampaign(): void
    {
        if ($this->confirmingDeleteCampaignId === null) {
            return;
        }

        $campaign = $this->client->resolveClientCampaign($this->confirmingDeleteCampaignId);
        $this->authorize('delete', $campaign);

        $campaign->delete();
        $this->confirmingDeleteCampaignId = null;

        if ($this->activeTab === 'content') {
            $this->linkedContentLoaded = false;
            $this->loadLinkedContent();
        }

        session()->flash('status', 'Campaign deleted.');
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

        $linkedMediaQuery = $this->linkedMediaQuery();

        return [
            'linked_posts' => (clone $linkedMediaQuery)->count(),
            'active_proposals' => $this->client->proposals()->where('status', ProposalStatus::Sent)->count(),
            'pending_invoices' => $pendingInvoiceQuery->count(),
            'pending_invoice_total' => (float) $pendingInvoiceQuery->sum('total'),
        ];
    }

    private function linkedContentMedia(): EloquentCollection
    {
        return $this->linkedMediaQuery()
            ->with('campaigns')
            ->orderByDesc('published_at')
            ->get();
    }

    private function groupedLinkedContent(EloquentCollection $linkedContentMedia): Collection
    {
        $campaigns = $this->client->campaigns()
            ->with([
                'instagramMedia' => fn ($builder) => $builder->orderByDesc('published_at'),
            ])
            ->orderBy('name')
            ->get();

        $groups = $campaigns
            ->filter(fn (Campaign $campaign): bool => $campaign->instagramMedia->isNotEmpty())
            ->map(function (Campaign $campaign): array {
                $media = collect($campaign->instagramMedia->all());

                return [
                    'key' => 'campaign-'.$campaign->id,
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'media' => $media,
                    'total_posts' => $media->count(),
                    'total_reach' => (int) $media->sum('reach'),
                ];
            })
            ->values();

        $uncategorizedMedia = $linkedContentMedia
            ->filter(function (InstagramMedia $media): bool {
                return $media->campaigns
                    ->where('client_id', $this->client->id)
                    ->isEmpty();
            })
            ->values();

        if ($uncategorizedMedia->isNotEmpty()) {
            $groups->push([
                'key' => 'uncategorized',
                'campaign_id' => null,
                'campaign_name' => 'Uncategorized',
                'media' => $uncategorizedMedia,
                'total_posts' => $uncategorizedMedia->count(),
                'total_reach' => (int) $uncategorizedMedia->sum('reach'),
            ]);
        }

        return $groups;
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

    private function linkedMediaQuery(): Builder
    {
        return InstagramMedia::query()
            ->select('instagram_media.*')
            ->whereHas('campaigns', fn (Builder $builder): Builder => $builder->where('campaigns.client_id', $this->client->id))
            ->distinct();
    }

    private function campaigns(): EloquentCollection
    {
        return $this->client->campaigns()
            ->with('proposal')
            ->withCount('instagramMedia')
            ->orderBy('name')
            ->get();
    }

    private function campaignProposals(): EloquentCollection
    {
        return Proposal::query()
            ->where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'status']);
    }

}
