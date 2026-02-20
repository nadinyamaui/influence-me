<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Analytics</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Campaign performance and audience insights for content linked to {{ $client->name }}.
                </p>
            </div>
        </div>
    </section>

    @if (! $clientAnalytics['has_linked_content'])
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-300">
            No linked content available yet. Linked campaign content is required before analytics can be displayed.
        </section>
    @else
        <section class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Linked Posts</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($clientAnalytics['summary']['total_linked_posts']) }}</p>
                </article>
                <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Reach</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($clientAnalytics['summary']['total_reach']) }}</p>
                </article>
                <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Impressions</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($clientAnalytics['summary']['total_impressions']) }}</p>
                </article>
                <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Avg Engagement Rate</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($clientAnalytics['summary']['average_engagement_rate'], 2) }}%</p>
                </article>
            </div>

            <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Performance Over Time</h2>

                @if (count($clientAnalytics['trend']['labels']) > 0)
                    <div
                        class="mt-4 h-64"
                        wire:key="portal-client-analytics-engagement-{{ md5(json_encode($clientAnalytics['trend'])) }}"
                        x-data="portalClientAnalyticsEngagementChart(@js($clientAnalytics['trend']))"
                        x-init="init()"
                    >
                        <canvas x-ref="canvas" role="img" aria-label="Client portal engagement trend chart"></canvas>
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-300">
                        No published linked posts yet, so engagement trend data is not available.
                    </div>
                @endif
            </article>

            <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Campaign Breakdown</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format(count($clientAnalytics['campaign_breakdown'])) }} campaigns</p>
                </div>

                @if (count($clientAnalytics['campaign_breakdown']) === 0)
                    <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No campaign-level analytics available yet.</p>
                @else
                    <div class="mt-4 space-y-2 sm:hidden">
                        @foreach ($clientAnalytics['campaign_breakdown'] as $item)
                            <article wire:key="portal-client-analytics-campaign-card-{{ $item['campaign_id'] }}" class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['campaign_name'] }}</p>
                                <dl class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <dt class="text-zinc-500 dark:text-zinc-300">Posts</dt>
                                        <dd class="mt-1 text-zinc-700 dark:text-zinc-200">{{ number_format($item['posts']) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-zinc-500 dark:text-zinc-300">Reach</dt>
                                        <dd class="mt-1 text-zinc-700 dark:text-zinc-200">{{ number_format($item['reach']) }}</dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-zinc-500 dark:text-zinc-300">Avg Engagement</dt>
                                        <dd class="mt-1 text-zinc-700 dark:text-zinc-200">{{ number_format($item['average_engagement_rate'], 2) }}%</dd>
                                    </div>
                                </dl>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-4 hidden overflow-x-auto sm:block">
                        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Campaign</th>
                                    <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Posts</th>
                                    <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Reach</th>
                                    <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Avg Engagement</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($clientAnalytics['campaign_breakdown'] as $item)
                                    <tr wire:key="portal-client-analytics-campaign-{{ $item['campaign_id'] }}">
                                        <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">{{ $item['campaign_name'] }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($item['posts']) }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($item['reach']) }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($item['average_engagement_rate'], 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Audience Demographics</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Age, gender, city, and country distribution from linked Instagram accounts.</p>
            </div>

            @if (! $audienceDemographics['has_data'])
                <div class="mt-4 flex h-56 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-zinc-400">
                    Audience demographics data is not available yet for linked accounts.
                </div>
            @else
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Age Distribution</h3>
                        <div
                            class="mt-3 h-64"
                            wire:key="portal-demographics-age-{{ md5(json_encode($audienceDemographics['age'])) }}"
                            x-data="portalAudienceAgeChart(@js($audienceDemographics['age']))"
                            x-init="init()"
                        >
                            <canvas x-ref="canvas" role="img" aria-label="Audience age distribution chart"></canvas>
                        </div>
                    </article>

                    <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Gender Breakdown</h3>
                        <div
                            class="mt-3 h-64"
                            wire:key="portal-demographics-gender-{{ md5(json_encode($audienceDemographics['gender'])) }}"
                            x-data="portalAudienceGenderChart(@js($audienceDemographics['gender']))"
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
                                wire:key="portal-demographics-city-{{ md5(json_encode($audienceDemographics['city'])) }}"
                                x-data="portalAudienceHorizontalBarChart(@js($audienceDemographics['city']), 'Top Cities', '#0ea5e9')"
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
                                wire:key="portal-demographics-country-{{ md5(json_encode($audienceDemographics['country'])) }}"
                                x-data="portalAudienceHorizontalBarChart(@js($audienceDemographics['country']), 'Top Countries', '#10b981')"
                                x-init="init()"
                            >
                                <canvas x-ref="canvas" role="img" aria-label="Top countries audience chart"></canvas>
                            </div>
                        @endif
                    </article>
                </div>
            @endif
        </section>
    @endif
</div>

<script>
    if (! window.portalClientAnalyticsEngagementChart) {
        window.portalClientAnalyticsEngagementChart = (series) => ({
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

    if (! window.portalAudienceAgeChart) {
        window.portalAudienceAgeChart = (series) => ({
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

    if (! window.portalAudienceGenderChart) {
        window.portalAudienceGenderChart = (series) => ({
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

    if (! window.portalAudienceHorizontalBarChart) {
        window.portalAudienceHorizontalBarChart = (series, label, color) => ({
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
