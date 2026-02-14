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
                <article wire:key="content-media-{{ $item->id }}" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
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
                </article>
            @endforeach
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            {{ $media->links() }}
        </section>
    @endif
</div>
