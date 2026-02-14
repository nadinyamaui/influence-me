@php
    use App\Enums\MediaType;
    use Illuminate\Support\Str;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Content</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Browse synced Instagram media and filter by account, type, and performance.</p>
    </div>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <flux:select wire:model.live="mediaType" :label="__('Media Type')">
                <option value="all">All</option>
                @foreach ($mediaTypeFilters as $mediaType)
                    @if ($mediaType !== 'all')
                        <option value="{{ $mediaType }}">{{ Str::of($mediaType)->headline() }}</option>
                    @endif
                @endforeach
            </flux:select>

            <flux:select wire:model.live="accountId" :label="__('Instagram Account')">
                <option value="all">All Accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ '@'.$account->username }}</option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.live="dateFrom" :label="__('Date From')" />

            <flux:input type="date" wire:model.live="dateTo" :label="__('Date To')" />

            <flux:select wire:model.live="sortBy" :label="__('Sort By')">
                @foreach ($sortOptions as $sortValue => $label)
                    <option value="{{ $sortValue }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($media->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No content synced yet. Connect an Instagram account and run a sync.</h2>
        </section>
    @else
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($media as $item)
                <button
                    type="button"
                    wire:key="content-media-{{ $item->id }}"
                    wire:click="openDetailModal({{ $item->id }})"
                    class="overflow-hidden rounded-2xl border border-zinc-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="relative aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if ($item->thumbnail_url || $item->media_url)
                            <img
                                src="{{ $item->thumbnail_url ?? $item->media_url }}"
                                alt="{{ $item->caption ? Str::limit($item->caption, 50) : 'Instagram media' }}"
                                class="h-full w-full object-cover"
                            >
                        @endif

                        <div class="absolute left-2 top-2 inline-flex items-center gap-1 rounded-full bg-zinc-950/80 px-2 py-1 text-xs font-medium text-white">
                            @if ($item->media_type === MediaType::Reel)
                                <span>▶</span>
                            @endif
                            <span>{{ Str::of($item->media_type->value)->headline() }}</span>
                        </div>

                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-zinc-950/90 to-transparent px-3 py-2 text-xs font-medium text-white">
                            <span>{{ number_format($item->like_count) }} likes</span>
                            <span>{{ number_format($item->comments_count) }} comments</span>
                            <span>{{ number_format($item->reach) }} reach</span>
                        </div>
                    </div>

                    <div class="space-y-2 p-3">
                        <p class="min-h-10 text-sm text-zinc-700 dark:text-zinc-200">{{ Str::limit($item->caption ?? 'No caption', 50) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-300">{{ $item->published_at?->format('M j, Y g:i A') ?? 'Unpublished' }}</p>
                    </div>
                </button>
            @endforeach
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            {{ $media->links() }}
        </section>
    @endif

    @if ($showDetailModal && $selectedMedia)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/60 p-4">
            <div class="grid w-full max-w-5xl gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xl lg:grid-cols-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="space-y-4">
                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                        @if ($selectedMedia->media_url || $selectedMedia->thumbnail_url)
                            <img
                                src="{{ $selectedMedia->media_url ?? $selectedMedia->thumbnail_url }}"
                                alt="{{ $selectedMedia->caption ? Str::limit($selectedMedia->caption, 60) : 'Instagram media preview' }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex aspect-square items-center justify-center text-sm text-zinc-500 dark:text-zinc-300">No media preview available.</div>
                        @endif
                    </div>

                    @if ($selectedMedia->permalink)
                        <a
                            href="{{ $selectedMedia->permalink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center text-sm font-medium text-sky-600 underline underline-offset-2 hover:text-sky-500 dark:text-sky-300 dark:hover:text-sky-200"
                        >
                            View on Instagram
                        </a>
                    @endif
                </div>

                <div class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Content Details</h2>
                        <div class="mt-3 max-h-36 overflow-y-auto rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200">
                            {{ $selectedMedia->caption ?? 'No caption.' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Likes</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->like_count) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Comments</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->comments_count) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Saved</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->saved_count) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Shares</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->shares_count) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Reach</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->reach) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Impressions</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($selectedMedia->impressions) }}</p></div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"><p class="text-zinc-500 dark:text-zinc-300">Engagement Rate</p><p class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float) $selectedMedia->engagement_rate, 2) }}%</p></div>
                    </div>

                    <dl class="grid gap-2 rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div class="flex items-center justify-between gap-2"><dt class="text-zinc-500 dark:text-zinc-300">Published</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedMedia->published_at?->format('M j, Y g:i A') ?? 'Unpublished' }}</dd></div>
                        <div class="flex items-center justify-between gap-2"><dt class="text-zinc-500 dark:text-zinc-300">Media Type</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ Str::of($selectedMedia->media_type->value)->headline() }}</dd></div>
                        <div class="flex items-center justify-between gap-2"><dt class="text-zinc-500 dark:text-zinc-300">Account</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ '@'.$selectedMedia->instagramAccount->username }}</dd></div>
                    </dl>

                    <section class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Linked Clients</h3>
                            <flux:button type="button" size="sm" variant="filled" disabled>
                                Link to Client
                            </flux:button>
                        </div>

                        @if ($selectedMedia->clients->isEmpty())
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">No linked clients yet.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($selectedMedia->clients as $client)
                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-800/60">
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $client->name }}</p>
                                            <p class="text-zinc-500 dark:text-zinc-300">{{ $client->pivot->campaign_name ?? 'Uncategorized' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="filled" wire:click="closeDetailModal">
                            Close
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
