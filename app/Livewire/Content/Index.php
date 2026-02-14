<?php

namespace App\Livewire\Content;

use App\Enums\MediaType;
use App\Models\InstagramMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $mediaType = 'all';

    public string $accountId = 'all';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public string $sortBy = 'most_recent';

    public ?int $selectedMediaId = null;

    public bool $showDetailModal = false;

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

    public function updatedDateFrom(?string $value): void
    {
        $this->dateFrom = blank($value) ? null : $value;
        $this->resetCursor();
    }

    public function updatedDateTo(?string $value): void
    {
        $this->dateTo = blank($value) ? null : $value;
        $this->resetCursor();
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
            'accounts' => $this->accounts(),
            'media' => $this->media(),
            'mediaTypeFilters' => $this->mediaTypeFilters(),
            'selectedMedia' => $this->selectedMedia(),
            'sortOptions' => $this->sortOptions(),
        ])->layout('layouts.app', [
            'title' => __('Content'),
        ]);
    }

    public function openDetailModal(int $mediaId): void
    {
        $media = $this->resolveMedia($mediaId);
        $this->authorize('view', $media);

        $this->selectedMediaId = $media->id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
    }

    private function media(): CursorPaginator
    {
        [$sortField, $sortDirection] = $this->sortMap()[$this->sortBy] ?? $this->sortMap()['most_recent'];

        $query = InstagramMedia::query()
            ->with('instagramAccount')
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()));

        if ($this->mediaType !== 'all' && in_array($this->mediaType, $this->mediaTypeFilters(), true)) {
            $query->where('media_type', $this->mediaType);
        }

        if ($this->accountId !== 'all') {
            $query->where('instagram_account_id', (int) $this->accountId);
        }

        if (filled($this->dateFrom)) {
            $query->whereDate('published_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('published_at', '<=', $this->dateTo);
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

    private function selectedMedia(): ?InstagramMedia
    {
        if ($this->selectedMediaId === null) {
            return null;
        }

        return InstagramMedia::query()
            ->with([
                'instagramAccount',
                'clients' => fn ($builder) => $builder
                    ->where('user_id', Auth::id())
                    ->orderBy('name'),
            ])
            ->whereKey($this->selectedMediaId)
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()))
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

    private function resolveMedia(int $mediaId): InstagramMedia
    {
        return InstagramMedia::query()
            ->whereKey($mediaId)
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()))
            ->firstOrFail();
    }
}
