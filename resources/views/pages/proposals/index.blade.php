@php
    use App\Enums\ProposalStatus;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Proposals</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage your proposals and track client responses.</p>
        </div>

        <flux:button :href="route('proposals.create')" variant="primary" title="New Proposal" aria-label="New Proposal" wire:navigate>
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('delete'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('delete') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model.live="status" :label="__('Status')">
                <option value="all">All</option>
                <option value="{{ ProposalStatus::Draft->value }}">Draft</option>
                <option value="{{ ProposalStatus::Sent->value }}">Sent</option>
                <option value="{{ ProposalStatus::Approved->value }}">Approved</option>
                <option value="{{ ProposalStatus::Rejected->value }}">Rejected</option>
                <option value="{{ ProposalStatus::Revised->value }}">Revised</option>
            </flux:select>

            <flux:select wire:model.live="client" :label="__('Client')">
                <option value="all">All</option>
                @foreach ($clients as $clientOption)
                    <option value="{{ $clientOption->id }}">{{ $clientOption->name }}</option>
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
                            <tr wire:key="proposal-row-{{ $proposal->id }}">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $proposal->title }}</span>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($proposal->campaigns_count) }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($proposal->scheduled_content_count ?? 0) }}</td>
                                <td class="px-4 py-3">
                                    @switch($proposal->status)
                                        @case(ProposalStatus::Draft)
                                            <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Draft</span>
                                            @break
                                        @case(ProposalStatus::Sent)
                                            <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">Sent</span>
                                            @break
                                        @case(ProposalStatus::Approved)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Approved</span>
                                            @break
                                        @case(ProposalStatus::Rejected)
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">Rejected</span>
                                            @break
                                        @case(ProposalStatus::Revised)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">Revised</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $proposal->updated_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if ($proposal->status === ProposalStatus::Draft)
                                            <a
                                                href="{{ route('proposals.edit', $proposal) }}"
                                                wire:navigate
                                                class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                                title="Edit"
                                                aria-label="Edit"
                                            >
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $proposal->id }})"
                                            class="inline-flex items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950/40"
                                            title="Delete"
                                            aria-label="Delete"
                                        >
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
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
                <flux:button type="button" variant="danger" wire:click="delete" title="Delete" aria-label="Delete">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
