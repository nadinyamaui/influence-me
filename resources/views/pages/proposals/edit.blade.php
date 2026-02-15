@php
    use Illuminate\Support\Str;
@endphp

<div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Edit Proposal</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Update proposal details, campaign planning, and scheduled content.</p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button type="button" variant="filled" wire:click="togglePreview">
                {{ $previewMode ? 'Edit Mode' : 'Preview' }}
            </flux:button>
            @if (! $this->isEditable())
                <flux:button type="button" variant="primary" wire:click="duplicate">
                    Duplicate
                </flux:button>
            @endif
            <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                Back
            </flux:button>
        </div>
    </div>

    @if (! $this->isEditable())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/50 dark:text-amber-200">
            This proposal is read-only because its status is {{ Str::of($proposal->status->value)->headline() }}.
        </div>
    @endif

    <form wire:submit="update" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-5 md:grid-cols-2">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="title" :disabled="! $this->isEditable()" required />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>Client</flux:label>
                <flux:select wire:model="client_id" :disabled="! $this->isEditable()" required>
                    <option value="">Select a client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="client_id" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Content</flux:label>
            <flux:textarea wire:model="content" rows="12" :disabled="! $this->isEditable()" required />
            <flux:description>Supports Markdown formatting</flux:description>
            <flux:error name="content" />
        </flux:field>

        @if ($previewMode)
            <section class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-950/40">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Preview</h2>
                <article class="prose prose-zinc mt-4 max-w-none dark:prose-invert">
                    {!! Str::markdown($content !== '' ? $content : 'Nothing to preview yet.') !!}
                </article>
            </section>
        @endif

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Campaigns</h2>
                @if ($this->isEditable())
                    <flux:button type="button" variant="filled" wire:click="addCampaign">Add Campaign</flux:button>
                @endif
            </div>

            <flux:error name="campaigns" />

            @foreach ($campaigns as $campaignIndex => $campaign)
                <article wire:key="edit-campaign-{{ $campaignIndex }}" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:field>
                            <flux:label>Existing Campaign (Optional)</flux:label>
                            <flux:select wire:model="campaigns.{{ $campaignIndex }}.id" :disabled="! $this->isEditable()">
                                <option value="">Create new campaign</option>
                                @foreach ($availableCampaigns as $existingCampaign)
                                    <option value="{{ $existingCampaign->id }}">{{ $existingCampaign->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="campaigns.{{ $campaignIndex }}.id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Campaign Name</flux:label>
                            <flux:input wire:model="campaigns.{{ $campaignIndex }}.name" :disabled="! $this->isEditable()" />
                            <flux:error name="campaigns.{{ $campaignIndex }}.name" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Campaign Description</flux:label>
                        <flux:textarea wire:model="campaigns.{{ $campaignIndex }}.description" rows="3" :disabled="! $this->isEditable()" />
                        <flux:error name="campaigns.{{ $campaignIndex }}.description" />
                    </flux:field>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Scheduled Content</h3>
                            @if ($this->isEditable())
                                <div class="flex items-center gap-2">
                                    <flux:button type="button" variant="filled" wire:click="addScheduledItem({{ $campaignIndex }})">Add Item</flux:button>
                                    <flux:button type="button" variant="danger" wire:click="removeCampaign({{ $campaignIndex }})">Remove Campaign</flux:button>
                                </div>
                            @endif
                        </div>

                        <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items" />

                        @foreach ($campaign['scheduled_items'] as $scheduledItemIndex => $scheduledItem)
                            <div wire:key="edit-campaign-{{ $campaignIndex }}-item-{{ $scheduledItemIndex }}" class="grid gap-4 rounded-lg border border-zinc-200 p-4 md:grid-cols-2 dark:border-zinc-700">
                                <flux:field>
                                    <flux:label>Title</flux:label>
                                    <flux:input wire:model="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.title" :disabled="! $this->isEditable()" required />
                                    <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.title" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Content Type</flux:label>
                                    <flux:select wire:model="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.media_type" :disabled="! $this->isEditable()" required>
                                        @foreach ($mediaTypes as $mediaType)
                                            <option value="{{ $mediaType->value }}">{{ Str::of($mediaType->value)->headline() }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.media_type" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Instagram Account</flux:label>
                                    <flux:select wire:model="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.instagram_account_id" :disabled="! $this->isEditable()" required>
                                        <option value="">Select account</option>
                                        @foreach ($instagramAccounts as $instagramAccount)
                                            <option value="{{ $instagramAccount->id }}">{{ $instagramAccount->username }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.instagram_account_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Scheduled At</flux:label>
                                    <flux:input type="datetime-local" wire:model="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.scheduled_at" :disabled="! $this->isEditable()" required />
                                    <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.scheduled_at" />
                                </flux:field>

                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label>Description</flux:label>
                                        <flux:textarea wire:model="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.description" rows="3" :disabled="! $this->isEditable()" />
                                        <flux:error name="campaigns.{{ $campaignIndex }}.scheduled_items.{{ $scheduledItemIndex }}.description" />
                                    </flux:field>
                                </div>

                                @if ($this->isEditable())
                                    <div class="md:col-span-2 flex justify-end">
                                        <flux:button type="button" variant="danger" wire:click="removeScheduledItem({{ $campaignIndex }}, {{ $scheduledItemIndex }})">
                                            Remove Item
                                        </flux:button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button type="button" variant="danger" wire:click="confirmDelete">
                Delete
            </flux:button>

            <div class="flex items-center gap-3">
                <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                    Cancel
                </flux:button>
                @if ($this->isEditable())
                    <flux:button type="submit" variant="primary">
                        Save as Draft
                    </flux:button>
                @endif
            </div>
        </div>
    </form>

    <flux:modal
        name="proposal-edit-delete-modal"
        wire:model="confirmingDelete"
        @close="cancelDelete"
        class="max-w-lg"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete proposal?</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">This action cannot be undone.</p>

        <div class="mt-5 flex justify-end gap-2">
            <flux:button type="button" variant="filled" wire:click="cancelDelete">
                Cancel
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="delete">
                Delete
            </flux:button>
        </div>
    </flux:modal>
</div>
