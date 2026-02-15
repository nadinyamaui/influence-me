@php
    use App\Enums\ProposalStatus;
    use App\Enums\ScheduledPostStatus;
    use Illuminate\Support\Str;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Schedule</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Plan and track upcoming posts in a chronological timeline.</p>
        </div>

        <flux:button type="button" variant="primary" wire:click="openCreateModal">
            Add Post
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <option value="all">All</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ Str::of($statusOption->value)->headline() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="clientFilter" :label="__('Client')">
                <option value="all">All Clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="accountFilter" :label="__('Instagram Account')">
                <option value="all">All Accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ '@'.$account->username }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="campaignFilter" :label="__('Campaign')">
                <option value="all">All Campaigns</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="mediaTypeFilter" :label="__('Media Type')">
                <option value="all">All</option>
                @foreach ($mediaTypes as $mediaTypeOption)
                    <option value="{{ $mediaTypeOption->value }}">{{ Str::of($mediaTypeOption->value)->headline() }}</option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
            <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
        </div>
    </section>

    @if ($postsByDay->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No scheduled posts found for the selected filters.</h2>
        </section>
    @else
        <section class="space-y-5">
            @foreach ($postsByDay as $day => $posts)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">{{ \Carbon\Carbon::parse($day)->format('F j, Y') }}</h2>

                    <div class="mt-4 space-y-3">
                        @foreach ($posts as $post)
                            <article wire:key="schedule-post-{{ $post->id }}" class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $post->scheduled_at->format('g:i A') }}</p>

                                            @if ($post->status === ScheduledPostStatus::Planned)
                                                <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">Planned</span>
                                            @elseif ($post->status === ScheduledPostStatus::Published)
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Published</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Cancelled</span>
                                            @endif

                                            <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ Str::of($post->media_type->value)->headline() }}</span>

                                            @if ($post->campaign?->proposal?->status instanceof ProposalStatus)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Proposal {{ Str::of($post->campaign->proposal->status->value)->headline() }}</span>
                                            @endif
                                        </div>

                                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $post->title }}</h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ Str::limit($post->description ?? 'No description', 150) }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-300">
                                            {{ $post->client?->name ?? 'No client' }}
                                            ·
                                            {{ $post->campaign?->name ?? 'No campaign' }}
                                            ·
                                            {{ '@'.$post->instagramAccount->username }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:button type="button" size="sm" variant="filled" wire:click="openEditModal({{ $post->id }})">Edit</flux:button>

                                        @if ($post->status !== ScheduledPostStatus::Published)
                                            <flux:button type="button" size="sm" variant="primary" wire:click="markPublished({{ $post->id }})">Mark as Published</flux:button>
                                        @endif

                                        @if ($post->status !== ScheduledPostStatus::Cancelled)
                                            <flux:button type="button" size="sm" variant="filled" wire:click="markCancelled({{ $post->id }})">Mark as Cancelled</flux:button>
                                        @endif

                                        <flux:button type="button" size="sm" variant="danger" wire:click="confirmDelete({{ $post->id }})">Delete</flux:button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <flux:modal
        name="schedule-post-modal"
        wire:model="showPostModal"
        @close="closePostModal"
        class="max-w-2xl"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $editingPostId ? 'Edit Scheduled Post' : 'Create Scheduled Post' }}</h2>

        <form wire:submit="savePost" class="mt-5 space-y-4">
            <flux:input wire:model="title" :label="__('Title')" />
            @error('title')
                <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <flux:textarea wire:model="description" :label="__('Description')" />
            @error('description')
                <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:select wire:model.live="clientId" :label="__('Client')">
                        <option value="">No client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('clientId')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:select wire:model="campaignId" :label="__('Campaign')">
                        <option value="">No campaign</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('campaignId')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:select wire:model="mediaType" :label="__('Media Type')">
                        @foreach ($mediaTypes as $mediaTypeOption)
                            <option value="{{ $mediaTypeOption->value }}">{{ Str::of($mediaTypeOption->value)->headline() }}</option>
                        @endforeach
                    </flux:select>
                    @error('mediaType')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:select wire:model="instagramAccountId" :label="__('Instagram Account')">
                        <option value="">Select account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ '@'.$account->username }}</option>
                        @endforeach
                    </flux:select>
                    @error('instagramAccountId')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:input type="datetime-local" wire:model="scheduledAt" :label="__('Date & Time')" />
                    @error('scheduledAt')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:select wire:model="status" :label="__('Status')">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption->value }}">{{ Str::of($statusOption->value)->headline() }}</option>
                        @endforeach
                    </flux:select>
                    @error('status')
                        <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button type="button" variant="filled" wire:click="closePostModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </form>
    </flux:modal>

    @if ($confirmingDeletePostId)
        <flux:modal
            name="schedule-delete-modal"
            :show="$confirmingDeletePostId !== null"
            @close="cancelDelete"
            class="max-w-md"
        >
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete this scheduled post?</h2>

            <div class="mt-5 flex justify-end gap-2">
                <flux:button type="button" variant="filled" wire:click="cancelDelete">
                    Cancel
                </flux:button>
                <flux:button type="button" variant="danger" wire:click="deletePost">
                    Delete
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
