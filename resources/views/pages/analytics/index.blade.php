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
        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Audience Growth Chart</h2>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Engagement Trend Chart</h2>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Average engagement rate over time with overall average reference.</p>
                </div>
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Average {{ number_format((float) $engagementTrend['average'], 2) }}%</p>
            </div>

            @if (count($engagementTrend['labels']) > 0)
                <div
                    class="mt-4 h-64"
                    wire:key="engagement-trend-{{ md5(json_encode([$period, $accountId, $engagementTrend['labels'], $engagementTrend['values']])) }}"
                    x-data="engagementTrendChart(@js($engagementTrend))"
                    x-init="init()"
                >
                    <canvas x-ref="canvas" role="img" aria-label="Engagement trend chart"></canvas>
                </div>
            @else
                <div class="mt-4 flex h-64 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                    No engagement data is available for this period.
                </div>
            @endif
        </article>

        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Best Performing Content</h2>
        </article>

        <article class="min-h-56 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Content Type Breakdown</h2>
        </article>
    </section>
</div>

<script>
    if (! window.engagementTrendChart) {
        window.engagementTrendChart = (series) => ({
            chart: null,
            init() {
                if (! window.Chart) {
                    return;
                }

                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'line',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                label: 'Engagement Rate',
                                data: series.values,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.18)',
                                borderWidth: 2,
                                pointRadius: 3,
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Overall Average',
                                data: series.average_line,
                                borderColor: '#0f766e',
                                borderDash: [6, 6],
                                borderWidth: 1.5,
                                pointRadius: 0,
                                tension: 0,
                                fill: false,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                            },
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => `${value}%`,
                                },
                            },
                        },
                    },
                });
            },
        });
    }
</script>
