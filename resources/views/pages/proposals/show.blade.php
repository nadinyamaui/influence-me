@php
    use App\Enums\ProposalStatus;
    use Illuminate\Support\Str;
@endphp

<div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('send'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('send') }}
        </div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $proposal->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $this->statusClasses() }}">{{ $this->statusLabel() }}</span>
                <span>{{ $proposal->client?->name ?? 'No client selected' }}</span>
                <span>Created {{ $proposal->created_at->format('M j, Y g:i A') }}</span>
                <span>Updated {{ $proposal->updated_at->format('M j, Y g:i A') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:button :href="route('proposals.index')" wire:navigate variant="filled">
                Back
            </flux:button>

            @if ($proposal->status === ProposalStatus::Draft)
                <flux:button :href="route('proposals.edit', $proposal)" wire:navigate variant="filled">
                    Edit
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="confirmSend">
                    Send to Client
                </flux:button>
            @elseif ($proposal->status === ProposalStatus::Revised)
                <flux:button :href="route('proposals.edit', $proposal)" wire:navigate variant="filled">
                    Edit
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="confirmSend">
                    Send Again
                </flux:button>
            @elseif ($proposal->status === ProposalStatus::Sent)
                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                    Waiting for response...
                </span>
            @elseif ($proposal->status === ProposalStatus::Approved)
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                    Approved
                </span>
            @elseif ($proposal->status === ProposalStatus::Rejected)
                <span class="inline-flex rounded-full bg-rose-100 px-3 py-1.5 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">
                    Rejected
                </span>
            @endif
        </div>
    </div>

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Proposal Content</h2>
        <article class="prose prose-zinc mt-4 max-w-none dark:prose-invert">
            {!! Str::markdown($proposal->content) !!}
        </article>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Campaign Plan</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ number_format($proposal->campaigns->count()) }} campaigns · {{ number_format($this->totalScheduledItems()) }} scheduled items
            </p>
        </div>

        @if ($proposal->campaigns->isEmpty())
            <div class="mt-5 rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                No campaigns are linked to this proposal yet.
            </div>
        @else
            <div class="mt-5 space-y-4">
                @foreach ($proposal->campaigns as $campaign)
                    <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $campaign->name }}</h3>
                                @if (filled($campaign->description))
                                    <p class="mt-1 whitespace-pre-wrap text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->description }}</p>
                                @endif
                            </div>
                            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">
                                {{ number_format($campaign->scheduledPosts->count()) }} scheduled
                            </p>
                        </div>

                        @if ($campaign->scheduledPosts->isEmpty())
                            <div class="mt-4 rounded-lg border border-dashed border-zinc-300 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                                No scheduled content for this campaign.
                            </div>
                        @else
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Title</th>
                                            <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Media Type</th>
                                            <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Account</th>
                                            <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Date/Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($campaign->scheduledPosts as $scheduledPost)
                                            <tr>
                                                <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">{{ $scheduledPost->title }}</td>
                                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-200">{{ Str::of($scheduledPost->media_type->value)->headline() }}</td>
                                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-200">{{ $scheduledPost->instagramAccount?->username ?? 'Unknown account' }}</td>
                                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-200">{{ $scheduledPost->scheduled_at->format('M j, Y g:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if ($proposal->status === ProposalStatus::Revised && filled($proposal->revision_notes))
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/50">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-200">Revision Notes</h2>
            <p class="mt-2 text-sm font-medium text-amber-800 dark:text-amber-100">The client requested changes:</p>
            <p class="mt-2 whitespace-pre-wrap text-sm text-amber-800 dark:text-amber-100">{{ $proposal->revision_notes }}</p>
        </section>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-4 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
        <p>Sent at: {{ $proposal->sent_at?->format('M j, Y g:i A') ?? 'Not sent yet' }}</p>
        <p class="mt-1">Responded at: {{ $proposal->responded_at?->format('M j, Y g:i A') ?? 'No response yet' }}</p>
    </section>

    <flux:modal
        name="proposal-send-modal"
        wire:model="confirmingSend"
        @close="cancelSend"
        class="max-w-lg"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Send proposal to client?</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
            Send this proposal to {{ $proposal->client?->name ?? 'this client' }} at {{ $proposal->client?->email ?? 'no email on file' }}?
        </p>

        <div class="mt-5 flex justify-end gap-2">
            <flux:button type="button" variant="filled" wire:click="cancelSend">
                Cancel
            </flux:button>
            <flux:button type="button" variant="primary" wire:click="send">
                Send
            </flux:button>
        </div>
    </flux:modal>
</div>
