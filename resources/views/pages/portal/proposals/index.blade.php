@php
    use App\Enums\ProposalStatus;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Proposals</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Review proposals shared with your team.</p>
        </div>
    </div>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="max-w-xs">
            <flux:select wire:model.live="status" :label="__('Status')">
                <option value="all">All</option>
                @foreach ($filterStatuses as $filterStatus)
                    <option value="{{ $filterStatus }}">{{ ProposalStatus::from($filterStatus)->label() }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($proposals->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No proposals available yet.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Title</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Campaigns</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Scheduled Content</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Received</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($proposals as $proposal)
                            <tr wire:key="portal-proposal-row-{{ $proposal->id }}">
                                <td class="px-4 py-3">
                                    <a
                                        href="{{ route('portal.proposals.show', $proposal) }}"
                                        wire:navigate
                                        class="font-medium text-zinc-900 underline-offset-2 hover:underline dark:text-zinc-100"
                                    >
                                        {{ $proposal->title }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($proposal->campaigns_count) }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($proposal->scheduled_content_count ?? 0) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $proposal->status->badgeClasses() }}">
                                        {{ $proposal->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->sent_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button :href="route('portal.proposals.show', $proposal)" wire:navigate variant="filled" size="sm">
                                        View
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $proposals->links() }}
            </div>
        </section>
    @endif
</div>
