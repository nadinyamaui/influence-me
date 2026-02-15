<?php

namespace App\Livewire\Content;

use App\Enums\MediaType;
use App\Livewire\Forms\CampaignForm;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Services\Content\ContentClientLinkService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $mediaType = 'all';

    public string $accountId = 'all';

    public string $clientId = 'all';

    public array $dateRange = [
        'start' => null,
        'end' => null,
    ];

    public string $sortBy = 'most_recent';

    public ?int $selectedMediaId = null;

    public bool $showDetailModal = false;

    public bool $selectionMode = false;

    public array $selectedMediaIds = [];

    public bool $showLinkModal = false;

    public bool $linkingBatch = false;

    public ?string $linkClientId = null;

    public ?string $linkCampaignId = null;

    public bool $showInlineCampaignForm = false;

    public CampaignForm $campaignForm;

    public string $linkNotes = '';

    public ?int $confirmingUnlinkClientId = null;

    public function updatedMediaType(string $value): void
    {
        if (! in_array($value, $this->mediaTypeFilters(), true)) {
            $this->mediaType = 'all';
        }

        $this->resetCursor();
    }

    public function updatedAccountId(string $value): void
    {
        if ($value !== 'all') {
            $accountId = (int) $value;
            $hasAccount = Auth::user()->instagramAccounts()->whereKey($accountId)->exists();

            if (! $hasAccount) {
                $this->accountId = 'all';
            }
        }

        $this->resetCursor();
    }

    public function updatedDateRange(mixed $value): void
    {
        $range = $this->normalizeDateRangeValue($value);
        $this->dateRange = $range;

        $this->resetCursor();
    }

    public function updatedClientId(string $value): void
    {
        if ($value !== 'all' && $value !== 'without_clients') {
            $clientId = (int) $value;
            $hasClient = Auth::user()->clients()->whereKey($clientId)->exists();

            if (! $hasClient) {
                $this->clientId = 'all';
            }
        }

        $this->resetCursor();
    }

    private function normalizeDateRangeValue(mixed $value): array
    {
        $start = null;
        $end = null;

        if (is_string($value)) {
            $parts = array_map('trim', explode('/', $value, 2));
            $start = $parts[0] ?? null;
            $end = $parts[1] ?? null;
        } elseif (is_array($value)) {
            $start = $value['start'] ?? null;
            $end = $value['end'] ?? null;
        }

        return [
            'start' => blank($start) ? null : (string) $start,
            'end' => blank($end) ? null : (string) $end,
        ];
    }

    public function updatedSortBy(string $value): void
    {
        if (! array_key_exists($value, $this->sortMap())) {
            $this->sortBy = 'most_recent';
        }

        $this->resetCursor();
    }

    public function render()
    {
        return view('pages.content.index', [
            'availableClients' => $this->availableClients(),
            'accounts' => $this->accounts(),
            'media' => $this->media(),
            'mediaTypeFilters' => $this->mediaTypeFilters(),
            'selectedMedia' => $this->selectedMedia(),
            'sortOptions' => $this->sortOptions(),
            'unlinkClient' => $this->unlinkClient(),
            'linkCampaigns' => $this->linkCampaignOptions(),
        ])->layout('layouts.app', [
            'title' => __('Content'),
        ]);
    }

    public function updatedLinkClientId(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->linkCampaignId = null;
            $this->showInlineCampaignForm = false;
            $this->campaignForm->clear(clearProposal: false);

            return;
        }

        $ownsClient = Auth::user()->clients()->whereKey((int) $value)->exists();

        if (! $ownsClient) {
            $this->linkClientId = null;
            $this->linkCampaignId = null;
            $this->showInlineCampaignForm = false;
            $this->campaignForm->clear(clearProposal: false);
        } elseif ($this->linkCampaignId !== null && ! $this->linkCampaignOptions()->contains('id', (int) $this->linkCampaignId)) {
            $this->linkCampaignId = null;
        }
    }

    public function openDetailModal(int $mediaId): void
    {
        $media = InstagramMedia::resolveForUser($mediaId);
        $this->authorize('view', $media);

        $this->selectedMediaId = $media->id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
    }

    public function toggleSelectionMode(): void
    {
        if ($this->selectionMode) {
            $this->cancelSelectionMode();

            return;
        }

        $this->selectionMode = true;
        $this->showDetailModal = false;
    }

    public function cancelSelectionMode(): void
    {
        $this->selectionMode = false;
        $this->selectedMediaIds = [];
    }

    public function toggleSelectedMedia(int $mediaId): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $media = InstagramMedia::resolveForUser($mediaId);
        $this->authorize('linkToClient', $media);

        $selectedIds = array_values(array_unique(array_map('intval', $this->selectedMediaIds)));

        if (in_array($media->id, $selectedIds, true)) {
            $selectedIds = array_values(array_filter(
                $selectedIds,
                fn (int $selectedId): bool => $selectedId !== $media->id,
            ));
        } else {
            $selectedIds[] = $media->id;
        }

        $this->selectedMediaIds = $selectedIds;
    }

    public function openBatchLinkModal(): void
    {
        if (count($this->selectedMediaIds) === 0) {
            $this->addError('linkSelection', 'Select at least one media item to link.');

            return;
        }

        $this->resetErrorBag('linkSelection');
        $this->linkingBatch = true;
        $this->resetLinkForm();
        $this->showLinkModal = true;
    }

    public function openSingleLinkModal(): void
    {
        if ($this->selectedMediaId === null) {
            return;
        }

        $media = InstagramMedia::resolveForUser($this->selectedMediaId);
        $this->authorize('linkToClient', $media);

        $this->linkingBatch = false;
        $this->resetErrorBag('linkSelection');
        $this->resetLinkForm();
        $this->showLinkModal = true;
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
        $this->resetLinkForm();
    }

    public function saveLink(ContentClientLinkService $linkService): void
    {
        $validated = $this->validate([
            'linkClientId' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($builder) => $builder->where('user_id', Auth::id())),
            ],
            'linkCampaignId' => [
                'required',
                'integer',
                Rule::exists('campaigns', 'id')->where(function ($builder) {
                    if ($this->linkClientId !== null) {
                        $builder->where('client_id', (int) $this->linkClientId);
                    }

                    return $builder->whereIn('client_id', Auth::user()->clients()->select('id'));
                }),
            ],
            'linkNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $campaign = Campaign::resolveForUser((int) $validated['linkCampaignId']);
        $notes = blank($validated['linkNotes']) ? null : $validated['linkNotes'];

        if ($this->linkingBatch) {
            $mediaItems = $this->selectedBatchMedia();
            if ($mediaItems->isEmpty()) {
                $this->addError('linkSelection', 'Select at least one media item to link.');

                return;
            }

            foreach ($mediaItems as $media) {
                $this->authorize('linkToClient', $media);
            }

            $linkService->batchLink($user, $mediaItems, $campaign, $notes);

            $this->cancelSelectionMode();
            $this->showLinkModal = false;
            $this->resetLinkForm();
            session()->flash('status', 'Selected content linked to client.');

            return;
        }

        if ($this->selectedMediaId === null) {
            return;
        }

        $media = InstagramMedia::resolveForUser($this->selectedMediaId);
        $this->authorize('linkToClient', $media);

        $linkService->link($user, $media, $campaign, $notes);

        $this->showLinkModal = false;
        $this->resetLinkForm();
        session()->flash('status', 'Content linked to client.');
    }

    public function toggleInlineCampaignForm(): void
    {
        if ($this->linkClientId === null || $this->linkClientId === '') {
            $this->addError('linkClientId', 'Select a client before creating a campaign.');

            return;
        }

        $this->showInlineCampaignForm = ! $this->showInlineCampaignForm;

        if (! $this->showInlineCampaignForm) {
            $this->campaignForm->clear(clearProposal: false);
            $this->resetErrorBag(['campaignForm.name', 'campaignForm.description']);
        }
    }

    public function createInlineCampaign(): void
    {
        $validated = $this->validate([
            'linkClientId' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($builder) => $builder->where('user_id', Auth::id())),
            ],
        ]);

        $this->campaignForm->validateForClient(
            clientId: (int) $validated['linkClientId'],
            userId: (int) Auth::id(),
            includeProposal: false,
        );

        $client = $this->resolveClient((int) $validated['linkClientId']);
        $campaign = $client->campaigns()->create($this->campaignForm->payload(includeProposal: false) + ['proposal_id' => null]);

        $this->linkCampaignId = (string) $campaign->id;
        $this->showInlineCampaignForm = false;
        $this->campaignForm->clear(clearProposal: false);
        $this->resetErrorBag(['campaignForm.name', 'campaignForm.description']);
    }

    public function confirmUnlinkClient(int $clientId): void
    {
        if ($this->selectedMediaId === null) {
            return;
        }

        $media = InstagramMedia::resolveForUser($this->selectedMediaId);
        $this->authorize('linkToClient', $media);

        $client = $this->resolveClient($clientId);

        $this->confirmingUnlinkClientId = $client->id;
    }

    public function cancelUnlinkClient(): void
    {
        $this->confirmingUnlinkClientId = null;
    }

    public function unlinkFromClient(ContentClientLinkService $linkService): void
    {
        if ($this->selectedMediaId === null || $this->confirmingUnlinkClientId === null) {
            return;
        }

        $media = InstagramMedia::resolveForUser($this->selectedMediaId);
        $this->authorize('linkToClient', $media);

        $client = $this->resolveClient($this->confirmingUnlinkClientId);

        $linkService->unlink(Auth::user(), $media, $client);

        $this->confirmingUnlinkClientId = null;
        session()->flash('status', 'Content unlinked from client.');
    }

    private function media(): CursorPaginator
    {
        [$sortField, $sortDirection] = $this->sortMap()[$this->sortBy] ?? $this->sortMap()['most_recent'];
        $range = $this->normalizeDateRangeValue($this->dateRange);

        $query = InstagramMedia::query()
            ->with('instagramAccount')
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()));

        if ($this->mediaType !== 'all' && in_array($this->mediaType, $this->mediaTypeFilters(), true)) {
            $query->where('media_type', $this->mediaType);
        }

        if ($this->accountId !== 'all') {
            $query->where('instagram_account_id', (int) $this->accountId);
        }

        if ($this->clientId === 'without_clients') {
            $query->whereDoesntHave('campaigns', fn (Builder $builder): Builder => $builder
                ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', Auth::id())));
        } elseif ($this->clientId !== 'all') {
            $query->whereHas('campaigns', fn (Builder $builder): Builder => $builder
                ->where('campaigns.client_id', (int) $this->clientId)
                ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', Auth::id())));
        }

        if (filled($range['start'])) {
            $query->whereDate('published_at', '>=', $range['start']);
        }

        if (filled($range['end'])) {
            $query->whereDate('published_at', '<=', $range['end']);
        }

        return $query
            ->orderBy($sortField, $sortDirection)
            ->orderByDesc('id')
            ->cursorPaginate(24, ['*'], 'cursor');
    }

    private function accounts(): Collection
    {
        return Auth::user()->instagramAccounts()
            ->orderBy('username')
            ->get(['id', 'username']);
    }

    private function availableClients(): Collection
    {
        return Auth::user()->clients()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function selectedMedia(): ?InstagramMedia
    {
        if ($this->selectedMediaId === null) {
            return null;
        }

        return InstagramMedia::query()
            ->with([
                'instagramAccount',
                'campaigns' => fn ($builder) => $builder
                    ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', Auth::id()))
                    ->with('client')
                    ->orderBy('name'),
            ])
            ->whereKey($this->selectedMediaId)
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()))
            ->first();
    }

    private function unlinkClient(): ?Client
    {
        if ($this->confirmingUnlinkClientId === null) {
            return null;
        }

        return Auth::user()->clients()
            ->whereKey($this->confirmingUnlinkClientId)
            ->first();
    }

    private function mediaTypeFilters(): array
    {
        return array_merge(
            ['all'],
            array_map(
                static fn (MediaType $mediaType): string => $mediaType->value,
                MediaType::cases(),
            ),
        );
    }

    private function sortMap(): array
    {
        return [
            'most_recent' => ['published_at', 'desc'],
            'most_liked' => ['like_count', 'desc'],
            'highest_reach' => ['reach', 'desc'],
            'best_engagement' => ['engagement_rate', 'desc'],
        ];
    }

    private function sortOptions(): array
    {
        return [
            'most_recent' => 'Most Recent',
            'most_liked' => 'Most Liked',
            'highest_reach' => 'Highest Reach',
            'best_engagement' => 'Best Engagement',
        ];
    }

    private function resetCursor(): void
    {
        $this->resetPage(pageName: 'cursor');
    }

    private function selectedBatchMedia(): EloquentCollection
    {
        $selectedIds = array_values(array_unique(array_map('intval', $this->selectedMediaIds)));
        if ($selectedIds === []) {
            return new EloquentCollection;
        }

        return InstagramMedia::query()
            ->whereIn('id', $selectedIds)
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()))
            ->get();
    }

    private function resolveClient(int $clientId): Client
    {
        return Auth::user()->clients()
            ->whereKey($clientId)
            ->firstOrFail();
    }

    private function linkCampaignOptions(): Collection
    {
        if ($this->linkClientId === null || $this->linkClientId === '') {
            return collect();
        }

        return Campaign::query()
            ->where('client_id', (int) $this->linkClientId)
            ->whereIn('client_id', Auth::user()->clients()->select('id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function resetLinkForm(): void
    {
        $this->linkClientId = null;
        $this->linkCampaignId = null;
        $this->showInlineCampaignForm = false;
        $this->campaignForm->clear(clearProposal: false);
        $this->linkNotes = '';
        $this->resetErrorBag(['linkClientId', 'linkCampaignId', 'campaignForm.name', 'campaignForm.description', 'linkNotes']);
    }
}
