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
                <flux:navlist.item href="#" :current="$activeTab === 'campaigns'" wire:click.prevent="$set('activeTab', 'campaigns')">
                    Campaigns
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
                    @foreach ($linkedContentGroups as $group)
                        <section wire:key="client-content-group-{{ $group['key'] }}" class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">{{ $group['campaign_name'] }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ number_format($group['total_posts']) }} posts · {{ number_format($group['total_reach']) }} reach</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($group['media'] as $linkedMedia)
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

        @if ($activeTab === 'campaigns')
            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Campaigns</h2>
                    <flux:button type="button" variant="primary" wire:click="openCreateCampaignModal">
                        Add Campaign
                    </flux:button>
                </div>

                <div wire:loading wire:target="openCreateCampaignModal,openEditCampaignModal,saveCampaign,deleteCampaign" class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200">
                    Updating campaigns...
                </div>

                @if ($campaigns->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                        No campaigns yet. Add a campaign to organize this client's content.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($campaigns as $campaign)
                            <article wire:key="client-campaign-{{ $campaign->id }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $campaign->name }}</h3>
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $campaign->description ?? 'No description' }}</p>
                                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-300">
                                            {{ number_format($campaign->instagram_media_count) }} linked posts
                                            ·
                                            Created {{ $campaign->created_at->format('M j, Y') }}
                                        </p>
                                        @if ($campaign->proposal)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-300">
                                                Proposal: {{ $campaign->proposal->title }} ({{ Str::of($campaign->proposal->status->value)->headline() }})
                                            </p>
                                        @else
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-300">Proposal: Not linked</p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <flux:button type="button" size="sm" variant="filled" wire:click="openEditCampaignModal({{ $campaign->id }})">
                                            Edit
                                        </flux:button>
                                        <flux:button type="button" size="sm" variant="danger" wire:click="confirmDeleteCampaign({{ $campaign->id }})">
                                            Delete
                                        </flux:button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
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

    <flux:modal
        name="client-campaign-modal"
        wire:model="showCampaignModal"
        @close="closeCampaignModal"
        class="max-w-xl"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $editingCampaignId ? 'Edit Campaign' : 'Create Campaign' }}</h2>

        <form wire:submit="saveCampaign" class="mt-5 space-y-4">
            <flux:input wire:model="campaignName" :label="__('Campaign Name')" />
            @error('campaignName')
                <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <flux:textarea wire:model="campaignDescription" :label="__('Description (Optional)')" />
            @error('campaignDescription')
                <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <flux:select wire:model="campaignProposalId" :label="__('Proposal (Optional)')">
                <option value="">No linked proposal</option>
                @foreach ($campaignProposals as $proposal)
                    <option value="{{ $proposal->id }}">{{ $proposal->title }} ({{ Str::of($proposal->status->value)->headline() }})</option>
                @endforeach
            </flux:select>
            @error('campaignProposalId')
                <p class="text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-2 pt-2">
                <flux:button type="button" variant="filled" wire:click="closeCampaignModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </form>
    </flux:modal>

    @if ($confirmingDeleteCampaignId)
        <flux:modal
            name="client-campaign-delete-modal"
            :show="$confirmingDeleteCampaignId !== null"
            @close="cancelDeleteCampaign"
            class="max-w-md"
        >
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete this campaign?</h2>

            <div class="mt-5 flex justify-end gap-2">
                <flux:button type="button" variant="filled" wire:click="cancelDeleteCampaign">
                    Cancel
                </flux:button>
                <flux:button type="button" variant="danger" wire:click="deleteCampaign">
                    Delete
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
