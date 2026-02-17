<?php

namespace App\Livewire\Content;

use App\Enums\MediaType;
use App\Livewire\Forms\CampaignForm;
use App\Models\Campaign;
use App\Models\InstagramMedia;
use App\Models\User;
use App\Services\Content\ContentClientLinkService;
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

    public function mount(): void
    {
        $mediaId = request()->integer('media');

        if ($mediaId <= 0) {
            return;
        }

        $media = InstagramMedia::query()
            ->whereKey($mediaId)
            ->forUser((int) Auth::id())
            ->first();

        if ($media === null) {
            return;
        }

        $this->selectedMediaId = $media->id;
        $this->showDetailModal = true;
    }

    public function updatedMediaType(string $value): void
    {
        if (! in_array($value, MediaType::filters(), true)) {
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
        $selectedMedia = $this->selectedMedia();

        return view('pages.content.index', [
            'availableClients' => User::availableClients(),
            'accounts' => User::accounts(),
            'media' => $this->media(),
            'mediaTypeFilters' => MediaType::filters(),
            'selectedMedia' => $selectedMedia,
            'selectedMediaComparisons' => $this->selectedMediaComparisons($selectedMedia),
            'sortOptions' => $this->sortOptions(),
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

        $client = User::resolveClient((int) $validated['linkClientId']);
        $campaign = $client->campaigns()->create($this->campaignForm->payload(includeProposal: false) + ['proposal_id' => null]);

        $this->linkCampaignId = (string) $campaign->id;
        $this->showInlineCampaignForm = false;
        $this->campaignForm->clear(clearProposal: false);
        $this->resetErrorBag(['campaignForm.name', 'campaignForm.description']);
    }

    public function unlinkFromClient(int $clientId, ContentClientLinkService $linkService): void
    {
        if ($this->selectedMediaId === null) {
            return;
        }

        $media = InstagramMedia::resolveForUser($this->selectedMediaId);
        $this->authorize('linkToClient', $media);

        $client = User::resolveClient($clientId);

        $linkService->unlink(Auth::user(), $media, $client);

        session()->flash('status', 'Content unlinked from client.');
    }

    private function media(): CursorPaginator
    {
        [$sortField, $sortDirection] = $this->sortMap()[$this->sortBy] ?? $this->sortMap()['most_recent'];
        $range = $this->normalizeDateRangeValue($this->dateRange);
        $userId = (int) Auth::id();

        return InstagramMedia::query()
            ->withInstagramAccount()
            ->forUser($userId)
            ->filterByMediaType($this->mediaType)
            ->filterByAccount($this->accountId)
            ->filterByClient($this->clientId, $userId)
            ->publishedFrom($range['start'])
            ->publishedUntil($range['end'])
            ->sortForGallery($sortField, $sortDirection)
            ->cursorPaginate(24, ['*'], 'cursor');
    }

    private function selectedMedia(): ?InstagramMedia
    {
        if ($this->selectedMediaId === null) {
            return null;
        }

        return InstagramMedia::query()
            ->withInstagramAccount()
            ->withOwnedCampaignsForUser((int) Auth::id())
            ->whereKey($this->selectedMediaId)
            ->forUser((int) Auth::id())
            ->first();
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
            ->forUser((int) Auth::id())
            ->get();
    }

    private function linkCampaignOptions(): Collection
    {
        if ($this->linkClientId === null || $this->linkClientId === '') {
            return collect();
        }

        return Campaign::query()
            ->forClient((int) $this->linkClientId)
            ->forUser((int) Auth::id())
            ->orderedByName()
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

    private function selectedMediaComparisons(?InstagramMedia $media): array
    {
        if ($media === null) {
            return [];
        }

        $averages = InstagramMedia::query()
            ->accountAverageMetricsForRecentDays((int) $media->instagram_account_id, 90);

        return [
            'likes' => $this->comparisonMetric((float) $media->like_count, (float) ($averages['likes'] ?? 0)),
            'comments' => $this->comparisonMetric((float) $media->comments_count, (float) ($averages['comments'] ?? 0)),
            'reach' => $this->comparisonMetric((float) $media->reach, (float) ($averages['reach'] ?? 0)),
            'engagement_rate' => $this->comparisonMetric((float) $media->engagement_rate, (float) ($averages['engagement_rate'] ?? 0)),
        ];
    }

    private function comparisonMetric(float $value, float $average): array
    {
        if ($average <= 0.0) {
            return [
                'value' => $value,
                'average' => $average,
                'hasAverage' => false,
                'direction' => 'flat',
                'deltaPercent' => 0,
            ];
        }

        $deltaPercent = round((($value - $average) / $average) * 100);
        $direction = 'flat';

        if ($deltaPercent > 0) {
            $direction = 'up';
        } elseif ($deltaPercent < 0) {
            $direction = 'down';
        }

        return [
            'value' => $value,
            'average' => $average,
            'hasAverage' => true,
            'direction' => $direction,
            'deltaPercent' => (int) abs($deltaPercent),
        ];
    }
}
