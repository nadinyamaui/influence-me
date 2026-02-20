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
                <div class="w-full sm:w-56">
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

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Best Performing Content</h2>
                <div class="rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    @foreach ($topContentSortOptions as $value => $label)
                        <flux:button
                            type="button"
                            size="sm"
                            wire:click="$set('topContentSort', '{{ $value }}')"
                            :variant="$topContentSort === $value ? 'primary' : 'ghost'"
                        >
                            {{ $label }}
                        </flux:button>
                    @endforeach
                </div>
            </div>

            @if ($topContent->isEmpty())
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No posts found for the selected filters.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($topContent as $item)
                        <a
                            href="{{ route('content.index', ['media' => $item->id]) }}"
                            class="block rounded-xl border border-zinc-200 p-3 transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/60"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                <img
                                    src="{{ $item->thumbnail_url ?: $item->media_url }}"
                                    alt="Media thumbnail"
                                    class="h-20 w-20 shrink-0 rounded-lg object-cover"
                                >
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $item->media_type->badgeClasses() }}">{{ $item->media_type->label() }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->published_at?->format('M j, Y') ?? 'Unknown date' }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-zinc-700 dark:text-zinc-200">{{ \Illuminate\Support\Str::limit($item->caption ?? 'No caption', 60) }}</p>
                                    <div class="mt-2 flex flex-wrap gap-4 text-xs text-zinc-600 dark:text-zinc-300">
                                        <span>{{ number_format((float) $item->engagement_rate, 2) }}% engagement</span>
                                        <span>{{ number_format((int) $item->like_count) }} likes</span>
                                        <span>{{ number_format((int) $item->reach) }} reach</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Content Type Breakdown</h2>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Posts, reels, and stories distribution for the selected filters.</p>
                </div>
                <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($contentTypeBreakdown['total']) }} total</p>
            </div>

            @if ($contentTypeBreakdown['total'] > 0)
                <div class="mt-4 grid gap-6 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]">
                    <div
                        class="mx-auto h-72 w-full max-w-xs"
                        wire:key="content-type-breakdown-{{ md5(json_encode([$period, $accountId, $contentTypeBreakdown['values']])) }}"
                        x-data="contentTypeBreakdownChart(@js($contentTypeBreakdown))"
                        x-init="init()"
                    >
                        <canvas x-ref="canvas" role="img" aria-label="Content type breakdown chart"></canvas>
                    </div>

                    <div class="space-y-3">
                        @foreach ($contentTypeBreakdown['items'] as $item)
                            <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $item['label'] }}</p>
                                    </div>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-300">
                                        {{ number_format($item['count']) }} ({{ number_format($item['percentage'], 1) }}%)
                                    </p>
                                </div>
                                <div class="mt-2 grid gap-2 text-xs text-zinc-600 dark:text-zinc-300 sm:grid-cols-2">
                                    <p>Avg engagement: {{ number_format((float) $item['average_engagement_rate'], 2) }}%</p>
                                    <p>Avg reach: {{ number_format((int) $item['average_reach']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-4 flex h-64 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                    No content data is available for this period.
                </div>
            @endif
        </article>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Audience Demographics</h2>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Age, gender, city, and country audience distribution for the selected account filter.</p>
        </div>

        @if (! $audienceDemographics['has_data'])
            <div class="mt-4 flex h-56 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                Audience demographics data is not available yet. Run a sync to fetch data. Note: Requires 100+ followers.
            </div>
        @else
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Age Distribution</h3>
                    <div
                        class="mt-3 h-64"
                        wire:key="demographics-age-{{ md5(json_encode([$accountId, $audienceDemographics['age']])) }}"
                        x-data="audienceAgeChart(@js($audienceDemographics['age']))"
                        x-init="init()"
                    >
                        <canvas x-ref="canvas" role="img" aria-label="Audience age distribution chart"></canvas>
                    </div>
                </article>

                <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Gender Breakdown</h3>
                    <div
                        class="mt-3 h-64"
                        wire:key="demographics-gender-{{ md5(json_encode([$accountId, $audienceDemographics['gender']])) }}"
                        x-data="audienceGenderChart(@js($audienceDemographics['gender']))"
                        x-init="init()"
                    >
                        <canvas x-ref="canvas" role="img" aria-label="Audience gender breakdown chart"></canvas>
                    </div>
                </article>

                <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Top Cities</h3>
                    @if (count($audienceDemographics['city']['labels']) === 0)
                        <div class="mt-3 flex h-64 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 px-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                            No city demographic data is available yet.
                        </div>
                    @else
                        <div
                            class="mt-3 h-64"
                            wire:key="demographics-city-{{ md5(json_encode([$accountId, $audienceDemographics['city']])) }}"
                            x-data="audienceHorizontalBarChart(@js($audienceDemographics['city']), 'Top Cities', '#0ea5e9')"
                            x-init="init()"
                        >
                            <canvas x-ref="canvas" role="img" aria-label="Top cities audience chart"></canvas>
                        </div>
                    @endif
                </article>

                <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Top Countries</h3>
                    @if (count($audienceDemographics['country']['labels']) === 0)
                        <div class="mt-3 flex h-64 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 px-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                            No country demographic data is available yet.
                        </div>
                    @else
                        <div
                            class="mt-3 h-64"
                            wire:key="demographics-country-{{ md5(json_encode([$accountId, $audienceDemographics['country']])) }}"
                            x-data="audienceHorizontalBarChart(@js($audienceDemographics['country']), 'Top Countries', '#10b981')"
                            x-init="init()"
                        >
                            <canvas x-ref="canvas" role="img" aria-label="Top countries audience chart"></canvas>
                        </div>
                    @endif
                </article>
            </div>
        @endif
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

    if (! window.contentTypeBreakdownChart) {
        window.contentTypeBreakdownChart = (series) => ({
            chart: null,
            init() {
                if (! window.Chart) {
                    return;
                }

                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'doughnut',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                data: series.values,
                                backgroundColor: series.colors,
                                borderWidth: 0,
                                hoverOffset: 6,
                            },
                        ],
                    },
                    plugins: [{
                        id: 'center-total',
                        afterDraw: (chart) => {
                            const { ctx } = chart;
                            const x = chart.getDatasetMeta(0).data[0]?.x;
                            const y = chart.getDatasetMeta(0).data[0]?.y;

                            if (x === undefined || y === undefined) {
                                return;
                            }

                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#71717a';
                            ctx.font = '500 12px system-ui, sans-serif';
                            ctx.fillText('Total', x, y - 10);
                            ctx.fillStyle = '#18181b';
                            ctx.font = '700 20px system-ui, sans-serif';
                            ctx.fillText(`${series.total}`, x, y + 10);
                            ctx.restore();
                        },
                    }],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const total = series.total || 0;
                                        const count = context.raw || 0;
                                        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
                                        return `${context.label}: ${count} (${percentage}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            },
        });
    }

    if (! window.audienceAgeChart) {
        window.audienceAgeChart = (series) => ({
            chart: null,
            init() {
                if (! window.Chart) {
                    return;
                }

                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                label: 'Audience %',
                                data: series.values,
                                backgroundColor: '#6366f1',
                                borderRadius: 6,
                                maxBarThickness: 36,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
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

    if (! window.audienceGenderChart) {
        window.audienceGenderChart = (series) => ({
            chart: null,
            init() {
                if (! window.Chart) {
                    return;
                }

                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'doughnut',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                data: series.values,
                                backgroundColor: series.colors,
                                borderWidth: 0,
                                hoverOffset: 6,
                            },
                        ],
                    },
                    plugins: [{
                        id: 'center-total',
                        afterDraw: (chart) => {
                            const { ctx } = chart;
                            const x = chart.getDatasetMeta(0).data[0]?.x;
                            const y = chart.getDatasetMeta(0).data[0]?.y;

                            if (x === undefined || y === undefined) {
                                return;
                            }

                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#71717a';
                            ctx.font = '500 12px system-ui, sans-serif';
                            ctx.fillText('Audience', x, y - 10);
                            ctx.fillStyle = '#18181b';
                            ctx.font = '700 18px system-ui, sans-serif';
                            ctx.fillText(`${series.total}%`, x, y + 10);
                            ctx.restore();
                        },
                    }],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${context.raw}%`,
                                },
                            },
                        },
                    },
                });
            },
        });
    }

    if (! window.audienceHorizontalBarChart) {
        window.audienceHorizontalBarChart = (series, label, color) => ({
            chart: null,
            init() {
                if (! window.Chart) {
                    return;
                }

                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                label,
                                data: series.values,
                                backgroundColor: color,
                                borderRadius: 6,
                                maxBarThickness: 20,
                            },
                        ],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.raw}%`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
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
