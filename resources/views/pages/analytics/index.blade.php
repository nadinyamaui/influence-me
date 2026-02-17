<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Analytics</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Track top-line performance across connected Instagram accounts.</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                @foreach ($periodOptions as $value => $label)
                    <flux:button
                        type="button"
                        size="sm"
                        wire:click="$set('period', '{{ $value }}')"
                        :variant="$period === $value ? 'primary' : 'ghost'"
                    >
                        {{ $label }}
                    </flux:button>
                @endforeach
            </div>

            @if ($accounts->count() > 1)
                <div class="min-w-56">
                    <flux:select wire:model.live="accountId" :label="__('Account')">
                        <option value="all">All Accounts</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ '@'.$account->username }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Followers</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $formatted['total_followers'] }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $formatted['followers_change'] }}</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Posts</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_posts']) }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ number_format($summary['post_breakdown']['posts']) }} posts,
                {{ number_format($summary['post_breakdown']['reels']) }} reels,
                {{ number_format($summary['post_breakdown']['stories']) }} stories
            </p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Average Engagement Rate</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $formatted['average_engagement_rate'] }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Across selected posts in this period</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Reach</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $formatted['total_reach'] }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Combined reach for selected posts</p>
        </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Audience Growth Chart</h2>
            @if (count($chart['labels']) === 0)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No follower snapshots yet. Daily snapshots will populate this chart.</p>
            @else
                <div
                    wire:key="audience-growth-{{ $period }}-{{ $accountId }}"
                    class="mt-4 h-64"
                    x-data="audienceGrowthChart(@js($chart['labels']), @js($chart['data']))"
                    x-init="init()"
                >
                    <canvas x-ref="canvas" role="img" aria-label="Audience growth followers over time"></canvas>
                </div>
            @endif
        </article>

        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Engagement Trend Chart</h2>
        </article>

        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Best Performing Content</h2>
        </article>

        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Content Type Breakdown</h2>
        </article>
    </section>
</div>
