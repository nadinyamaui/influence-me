<?php

namespace App\Livewire\Schedule;

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Http\Requests\StoreScheduledPostRequest;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\ScheduledPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public string $statusFilter = 'all';

    public string $clientFilter = 'all';

    public string $accountFilter = 'all';

    public string $campaignFilter = 'all';

    public string $mediaTypeFilter = 'all';

    public array $dateRange = [
        'start' => null,
        'end' => null,
    ];

    public bool $showPostModal = false;

    public ?int $editingPostId = null;

    public ?int $confirmingDeletePostId = null;

    public string $title = '';

    public string $description = '';

    public ?string $clientId = null;

    public ?string $campaignId = null;

    public string $mediaType = MediaType::Post->value;

    public ?string $instagramAccountId = null;

    public ?string $scheduledAt = null;

    public string $status = ScheduledPostStatus::Planned->value;

    public function mount(): void
    {
        $this->authorize('viewAny', ScheduledPost::class);
    }

    public function render()
    {
        return view('pages.schedule.index', [
            'postsByDay' => $this->timelinePosts(),
            'clients' => $this->clients(),
            'accounts' => $this->accounts(),
            'campaigns' => $this->campaigns(),
            'mediaTypes' => MediaType::cases(),
            'statuses' => ScheduledPostStatus::cases(),
            'proposalStatuses' => ProposalStatus::cases(),
        ])->layout('layouts.app', [
            'title' => __('Schedule'),
        ]);
    }

    public function updatedClientFilter(string $value): void
    {
        if ($value !== 'all' && ! Auth::user()->clients()->whereKey((int) $value)->exists()) {
            $this->clientFilter = 'all';
        }
    }

    public function updatedAccountFilter(string $value): void
    {
        if ($value !== 'all' && ! Auth::user()->instagramAccounts()->whereKey((int) $value)->exists()) {
            $this->accountFilter = 'all';
        }
    }

    public function updatedCampaignFilter(string $value): void
    {
        if ($value !== 'all' && ! $this->campaigns()->contains('id', (int) $value)) {
            $this->campaignFilter = 'all';
        }
    }

    public function updatedMediaTypeFilter(string $value): void
    {
        if ($value !== 'all' && ! in_array($value, array_map(fn (MediaType $mediaType): string => $mediaType->value, MediaType::cases()), true)) {
            $this->mediaTypeFilter = 'all';
        }
    }

    public function updatedDateRange(mixed $value): void
    {
        $start = null;
        $end = null;

        if (is_string($value)) {
            $parts = array_map('trim', explode('/', $value, 2));
            $start = $parts[0] ?? null;
            $end = $parts[1] ?? null;
        }

        if (is_array($value)) {
            $start = $value['start'] ?? null;
            $end = $value['end'] ?? null;
        }

        $this->dateRange = [
            'start' => blank($start) ? null : (string) $start,
            'end' => blank($end) ? null : (string) $end,
        ];
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', ScheduledPost::class);

        $this->editingPostId = null;
        $this->title = '';
        $this->description = '';
        $this->clientId = null;
        $this->campaignId = null;
        $this->mediaType = MediaType::Post->value;
        $this->instagramAccountId = null;
        $this->scheduledAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->status = ScheduledPostStatus::Planned->value;
        $this->resetErrorBag();
        $this->showPostModal = true;
    }

    public function openEditModal(int $scheduledPostId): void
    {
        $post = $this->resolveOwnedPost($scheduledPostId);
        $this->authorize('update', $post);

        $this->editingPostId = $post->id;
        $this->title = $post->title;
        $this->description = $post->description ?? '';
        $this->clientId = $post->client_id !== null ? (string) $post->client_id : null;
        $this->campaignId = $post->campaign_id !== null ? (string) $post->campaign_id : null;
        $this->mediaType = $post->media_type->value;
        $this->instagramAccountId = (string) $post->instagram_account_id;
        $this->scheduledAt = $post->scheduled_at->format('Y-m-d\TH:i');
        $this->status = $post->status->value;
        $this->resetErrorBag();
        $this->showPostModal = true;
    }

    public function closePostModal(): void
    {
        $this->showPostModal = false;
        $this->resetErrorBag();
    }

    public function savePost(): void
    {
        if ($this->editingPostId === null) {
            $this->authorize('create', ScheduledPost::class);
        } else {
            $this->authorize('update', $this->resolveOwnedPost($this->editingPostId));
        }

        $isCreating = $this->editingPostId === null;

        $validated = $this->validate(array_merge(
            StoreScheduledPostRequest::rulesForLivewire($isCreating),
            [
                'clientId' => [
                    $isCreating ? 'required' : 'nullable',
                    Rule::exists('clients', 'id')->where(fn ($builder) => $builder->where('user_id', Auth::id())),
                ],
                'campaignId' => [
                    'nullable',
                    Rule::exists('campaigns', 'id')->where(function ($builder) {
                        $builder->whereIn('client_id', Auth::user()->clients()->select('id'));

                        if ($this->clientId !== null && $this->clientId !== '') {
                            $builder->where('client_id', (int) $this->clientId);
                        }

                        return $builder;
                    }),
                ],
                'instagramAccountId' => [
                    'required',
                    Rule::exists('instagram_accounts', 'id')->where(fn ($builder) => $builder->where('user_id', Auth::id())),
                ],
            ],
        ), [], [
            'clientId' => $this->clientId,
            'campaignId' => $this->campaignId,
            'instagramAccountId' => $this->instagramAccountId,
            'scheduledAt' => $this->scheduledAt,
            'mediaType' => $this->mediaType,
            'status' => $this->status,
        ]);

        if (($this->clientId === null || $this->clientId === '') && ($this->campaignId !== null && $this->campaignId !== '')) {
            $this->addError('campaignId', 'Select a client before selecting a campaign.');

            return;
        }

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'client_id' => $validated['clientId'] ?? null,
            'campaign_id' => $validated['campaignId'] ?? null,
            'media_type' => $validated['mediaType'],
            'instagram_account_id' => $validated['instagramAccountId'],
            'scheduled_at' => Carbon::parse($validated['scheduledAt']),
            'status' => $validated['status'],
        ];

        if ($this->editingPostId === null) {
            Auth::user()->scheduledPosts()->create($payload);
            session()->flash('status', 'Scheduled post created.');
        } else {
            $post = $this->resolveOwnedPost($this->editingPostId);
            $this->authorize('update', $post);
            $post->update($payload);
            session()->flash('status', 'Scheduled post updated.');
        }

        $this->closePostModal();
    }

    public function confirmDelete(int $scheduledPostId): void
    {
        $post = $this->resolveOwnedPost($scheduledPostId);
        $this->authorize('delete', $post);

        $this->confirmingDeletePostId = $post->id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletePostId = null;
    }

    public function deletePost(): void
    {
        if ($this->confirmingDeletePostId === null) {
            return;
        }

        $post = $this->resolveOwnedPost($this->confirmingDeletePostId);
        $this->authorize('delete', $post);

        $post->delete();
        $this->confirmingDeletePostId = null;

        session()->flash('status', 'Scheduled post deleted.');
    }

    public function markPublished(int $scheduledPostId): void
    {
        $post = $this->resolveOwnedPost($scheduledPostId);
        $this->authorize('update', $post);

        $post->update(['status' => ScheduledPostStatus::Published]);

        session()->flash('status', 'Scheduled post marked as published.');
    }

    public function markCancelled(int $scheduledPostId): void
    {
        $post = $this->resolveOwnedPost($scheduledPostId);
        $this->authorize('update', $post);

        $post->update(['status' => ScheduledPostStatus::Cancelled]);

        session()->flash('status', 'Scheduled post marked as cancelled.');
    }

    public function updatedClientId(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->campaignId = null;

            return;
        }

        $ownsClient = Auth::user()->clients()->whereKey((int) $value)->exists();

        if (! $ownsClient) {
            $this->clientId = null;
            $this->campaignId = null;

            return;
        }

        if ($this->campaignId !== null && $this->campaignId !== '' && ! $this->campaigns()->contains('id', (int) $this->campaignId)) {
            $this->campaignId = null;
        }
    }

    private function timelinePosts(): Collection
    {
        $posts = ScheduledPost::query()
            ->with([
                'client',
                'instagramAccount',
                'campaign.proposal',
            ])
            ->where('user_id', Auth::id())
            ->when($this->statusFilter !== 'all', fn (Builder $builder): Builder => $builder->where('status', $this->statusFilter))
            ->when($this->clientFilter !== 'all', fn (Builder $builder): Builder => $builder->where('client_id', (int) $this->clientFilter))
            ->when($this->accountFilter !== 'all', fn (Builder $builder): Builder => $builder->where('instagram_account_id', (int) $this->accountFilter))
            ->when($this->campaignFilter !== 'all', fn (Builder $builder): Builder => $builder->where('campaign_id', (int) $this->campaignFilter))
            ->when($this->mediaTypeFilter !== 'all', fn (Builder $builder): Builder => $builder->where('media_type', $this->mediaTypeFilter))
            ->when(filled($this->dateRange['start'] ?? null), fn (Builder $builder): Builder => $builder->whereDate('scheduled_at', '>=', (string) ($this->dateRange['start'] ?? null)))
            ->when(filled($this->dateRange['end'] ?? null), fn (Builder $builder): Builder => $builder->whereDate('scheduled_at', '<=', (string) ($this->dateRange['end'] ?? null)))
            ->orderBy('scheduled_at')
            ->get();

        return $posts->groupBy(fn (ScheduledPost $post): string => $post->scheduled_at->toDateString());
    }

    private function clients(): Collection
    {
        return Auth::user()->clients()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function accounts(): Collection
    {
        return Auth::user()->instagramAccounts()
            ->orderBy('username')
            ->get(['id', 'username']);
    }

    private function campaigns(): Collection
    {
        $query = Campaign::query()
            ->whereHas('client', fn (Builder $builder): Builder => $builder->where('user_id', Auth::id()))
            ->with(['client', 'proposal'])
            ->orderBy('name');

        if ($this->clientId !== null && $this->clientId !== '') {
            $query->where('client_id', (int) $this->clientId);
        }

        return $query->get(['id', 'client_id', 'proposal_id', 'name']);
    }

    private function resolveOwnedPost(int $scheduledPostId): ScheduledPost
    {
        return ScheduledPost::query()
            ->where('user_id', Auth::id())
            ->whereKey($scheduledPostId)
            ->firstOrFail();
    }
}
