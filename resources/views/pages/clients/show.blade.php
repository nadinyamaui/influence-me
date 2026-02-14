@php
    use App\Enums\ClientType;
    use Illuminate\Support\Str;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $client->name }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($client->type === ClientType::Brand)
                    <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">Brand</span>
                @else
                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Individual</span>
                @endif

                @if ($client->type === ClientType::Brand && filled($client->company_name))
                    <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $client->company_name }}</span>
                @endif
            </div>
        </div>

        <flux:button :href="route('clients.edit', $client)" variant="primary" wire:navigate>
            Edit
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('invite') || $errors->has('revoke'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('invite') ?? $errors->first('revoke') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Client Info</h2>

        <dl class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Email</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $client->email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Phone</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $client->phone ?? '—' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Notes</dt>
                <dd class="mt-1 whitespace-pre-wrap text-sm text-zinc-900 dark:text-zinc-100">{{ $client->notes ?? '—' }}</dd>
            </div>
        </dl>

        <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-800/60">
            @if ($hasPortalAccess)
                <p class="font-medium text-emerald-700 dark:text-emerald-300">Portal access: Active</p>
                <flux:button type="button" variant="danger" class="mt-3" wire:click="confirmRevokePortalAccess">
                    Revoke Portal Access
                </flux:button>
            @else
                <p class="font-medium text-zinc-800 dark:text-zinc-100">Portal access: No portal access</p>

                @if (blank($client->email))
                    <p class="mt-1 text-zinc-600 dark:text-zinc-300">Add an email to enable portal access.</p>
                @else
                    <flux:button type="button" variant="primary" class="mt-3" wire:click="inviteToPortal">
                        Invite to Portal
                    </flux:button>
                @endif
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto pb-2">
            <flux:navlist class="min-w-max flex-row gap-2" variant="outline">
                <flux:navlist.item href="#" :current="$activeTab === 'overview'" wire:click.prevent="$set('activeTab', 'overview')">
                    Overview
                </flux:navlist.item>
                <flux:navlist.item href="#" :current="$activeTab === 'content'" wire:click.prevent="$set('activeTab', 'content')">
                    Content
                </flux:navlist.item>
                <flux:navlist.item href="#" :current="$activeTab === 'proposals'" wire:click.prevent="$set('activeTab', 'proposals')">
                    Proposals
                </flux:navlist.item>
                <flux:navlist.item href="#" :current="$activeTab === 'invoices'" wire:click.prevent="$set('activeTab', 'invoices')">
                    Invoices
                </flux:navlist.item>
                <flux:navlist.item href="#" :current="$activeTab === 'analytics'" wire:click.prevent="$set('activeTab', 'analytics')">
                    Analytics
                </flux:navlist.item>
            </flux:navlist>
        </div>

        @if ($activeTab === 'overview')
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total linked posts</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['linked_posts']) }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Active proposals</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['active_proposals']) }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Pending invoices</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['pending_invoices']) }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">${{ number_format($summary['pending_invoice_total'], 2) }}</p>
                </div>
            </div>
        @endif

        @if ($activeTab === 'content')
            @if ($linkedContentGroups->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                    No content linked to this client yet. Go to the Content browser to link posts.
                </div>
            @else
                <div class="mt-5 grid gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total linked posts</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($linkedContentSummary['total_posts']) }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total reach</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($linkedContentSummary['total_reach']) }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total impressions</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($linkedContentSummary['total_impressions']) }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Average engagement rate</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($linkedContentSummary['average_engagement_rate'], 2) }}%</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($linkedContentGroups as $campaignName => $campaignMedia)
                        <section class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">{{ $campaignName }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ number_format($campaignMedia->count()) }} posts · {{ number_format($campaignMedia->sum('reach')) }} reach</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($campaignMedia as $linkedMedia)
                                    <article wire:key="client-content-{{ $linkedMedia->id }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/60">
                                        <div class="flex gap-3">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-zinc-200 dark:bg-zinc-700">
                                                @if ($linkedMedia->thumbnail_url || $linkedMedia->media_url)
                                                    <img src="{{ $linkedMedia->thumbnail_url ?? $linkedMedia->media_url }}" alt="{{ $linkedMedia->caption ? Str::limit($linkedMedia->caption, 30) : 'Instagram content' }}" class="h-full w-full object-cover">
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ Str::limit($linkedMedia->caption ?? 'No caption', 30) }}</p>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-300">{{ number_format($linkedMedia->like_count) }} likes · {{ number_format($linkedMedia->reach) }} reach</p>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-300">{{ number_format((float) $linkedMedia->engagement_rate, 2) }}% engagement</p>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-300">{{ $linkedMedia->published_at?->format('M j, Y') ?? 'Unpublished' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex justify-end">
                                            <flux:button type="button" size="sm" variant="danger" wire:click="unlinkContent({{ $linkedMedia->id }})">
                                                Unlink
                                            </flux:button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        @endif

        @if ($activeTab === 'proposals')
            <div class="mt-5 rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                Proposals tab coming soon.
            </div>
        @endif

        @if ($activeTab === 'invoices')
            <div class="mt-5 rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                Invoices tab coming soon.
            </div>
        @endif

        @if ($activeTab === 'analytics')
            <div class="mt-5 rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                Analytics tab coming soon.
            </div>
        @endif
    </section>

    <flux:modal
        name="client-revoke-portal-modal"
        wire:model="confirmingRevokePortalAccess"
        @close="cancelRevokePortalAccess"
        class="max-w-lg"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Revoke portal access?</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This removes the client user account and blocks portal access immediately.</p>

        <div class="mt-5 flex justify-end gap-2">
            <flux:button type="button" variant="filled" wire:click="cancelRevokePortalAccess">
                Cancel
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="revokePortalAccess">
                Revoke Access
            </flux:button>
        </div>
    </flux:modal>
</div>
