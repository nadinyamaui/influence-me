@php
    use App\Enums\ProposalStatus;
    use Illuminate\Support\Str;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Proposals</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Review client proposals, scope, and status before sending.</p>
        </div>

        <flux:button :href="url('/proposals/create')" variant="primary" wire:navigate>
            New Proposal
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model.live="status" :label="__('Status')">
                <option value="all">All</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">
                        {{ Str::of($statusOption->value)->headline() }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="clientId" :label="__('Client')">
                <option value="all">All</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($proposals->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No proposals yet. Create your first proposal to send to a client.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Title</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Campaigns</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Scheduled Content</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Created</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Last Updated</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($proposals as $proposal)
                            @php
                                $badgeStyles = match ($proposal->status) {
                                    ProposalStatus::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700/60 dark:text-zinc-100',
                                    ProposalStatus::Sent => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200',
                                    ProposalStatus::Approved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                                    ProposalStatus::Rejected => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
                                    ProposalStatus::Revised => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
                                };
                            @endphp
                            <tr wire:key="proposal-row-{{ $proposal->id }}">
                                <td class="px-4 py-3">
                                    <a
                                        href="{{ url('/proposals/'.$proposal->id) }}"
                                        class="font-medium text-blue-600 transition hover:underline dark:text-blue-400"
                                        wire:navigate
                                    >
                                        {{ $proposal->title }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->client->name }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($proposal->campaigns_count) }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format((int) $proposal->scheduled_content_count) }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $badgeStyles }} inline-flex rounded-full px-2.5 py-1 text-xs font-medium">
                                        {{ Str::of($proposal->status->value)->headline() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->updated_at->format('M j, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('/proposals/'.$proposal->id) }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            wire:navigate
                                        >
                                            View
                                        </a>

                                        @if ($proposal->status === ProposalStatus::Draft)
                                            <a
                                                href="{{ url('/proposals/'.$proposal->id.'/edit') }}"
                                                class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                                wire:navigate
                                            >
                                                Edit
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-md border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-400 dark:border-zinc-700 dark:text-zinc-500">
                                                Edit
                                            </span>
                                        @endif

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $proposal->id }})"
                                            class="inline-flex items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950/40"
                                        >
                                            Delete
                                        </button>
                                    </div>
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

    @if ($deletingProposalId)
        <flux:modal
            name="proposal-list-delete-modal"
            :show="$deletingProposalId !== null"
            @close="cancelDelete"
            class="max-w-md"
        >
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete proposal?</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                You are about to delete
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->deletingProposal()?->title ?? 'this proposal' }}</span>.
            </p>

            <div class="mt-5 flex justify-end gap-2">
                <flux:button type="button" variant="filled" wire:click="cancelDelete">
                    Cancel
                </flux:button>
                <flux:button type="button" variant="danger" wire:click="delete">
                    Delete
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
