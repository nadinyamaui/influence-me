@php
    use Illuminate\Support\Str;
@endphp

<div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Build Proposal</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $title }} for {{ optional($proposal->client)->name }}</p>
        </div>

        <div class="flex items-center gap-2">
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

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-3 md:grid-cols-3">
            <button type="button" wire:click="goToStep(1)" class="rounded-xl border px-4 py-3 text-left {{ $currentStep === 1 ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200' }}">
                <p class="text-xs uppercase tracking-wide">Step 1</p>
                <p class="mt-1 text-sm font-semibold">Details</p>
            </button>
            <button type="button" wire:click="goToStep(2)" class="rounded-xl border px-4 py-3 text-left {{ $currentStep === 2 ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200' }}">
                <p class="text-xs uppercase tracking-wide">Step 2</p>
                <p class="mt-1 text-sm font-semibold">Campaigns</p>
            </button>
            <button type="button" wire:click="goToStep(3)" class="rounded-xl border px-4 py-3 text-left {{ $currentStep === 3 ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200' }}">
                <p class="text-xs uppercase tracking-wide">Step 3</p>
                <p class="mt-1 text-sm font-semibold">Scheduled Posts</p>
            </button>
        </div>
    </section>

    <form wire:submit="update" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        @if ($currentStep === 1)
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

            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Proposal Details</h2>
                <flux:button type="button" variant="filled" wire:click="togglePreview">
                    {{ $previewMode ? 'Edit Mode' : 'Preview' }}
                </flux:button>
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
        @endif

        @if ($currentStep === 2)
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Campaigns</h2>
                    @if ($this->isEditable())
                        <flux:button type="button" variant="filled" wire:click="addCampaign">Add Campaign</flux:button>
                    @endif
                </div>

                <flux:error name="campaigns" />

                @foreach ($campaigns as $campaignIndex => $campaign)
                    <article wire:key="step-campaign-{{ $campaignIndex }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div class="grid gap-4">
                            <flux:field>
                                <flux:label>Campaign Name</flux:label>
                                <flux:input wire:model="campaigns.{{ $campaignIndex }}.name" :disabled="! $this->isEditable()" />
                                <flux:error name="campaigns.{{ $campaignIndex }}.name" />
                            </flux:field>
                        </div>

                        <flux:field class="mt-4">
                            <flux:label>Campaign Description</flux:label>
                            <flux:textarea wire:model="campaigns.{{ $campaignIndex }}.description" rows="3" :disabled="! $this->isEditable()" />
                            <flux:error name="campaigns.{{ $campaignIndex }}.description" />
                        </flux:field>

                        @if ($this->isEditable())
                            <div class="mt-4 flex justify-end">
                                <flux:button type="button" variant="danger" wire:click="removeCampaign({{ $campaignIndex }})">
                                    Remove Campaign
                                </flux:button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        @if ($currentStep === 3)
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Scheduled Posts</h2>
                    @if ($this->isEditable())
                        <flux:button type="button" variant="filled" wire:click="addScheduledItem">Add Scheduled Post</flux:button>
                    @endif
                </div>

                <flux:error name="campaigns" />

                @foreach ($scheduledItems as $scheduledItemIndex => $scheduledItem)
                    <article wire:key="step-scheduled-{{ $scheduledItemIndex }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:field>
                                <flux:label>Campaign</flux:label>
                                <flux:select wire:model="scheduledItems.{{ $scheduledItemIndex }}.campaign_index" :disabled="! $this->isEditable()" required>
                                    @foreach ($campaigns as $campaignIndex => $campaign)
                                        <option value="{{ $campaignIndex }}">
                                            {{ $campaign['name'] !== '' ? $campaign['name'] : 'Campaign '.($campaignIndex + 1) }}
                                        </option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            <flux:field>
                                <flux:label>Title</flux:label>
                                <flux:input wire:model="scheduledItems.{{ $scheduledItemIndex }}.title" :disabled="! $this->isEditable()" required />
                            </flux:field>

                            <flux:field>
                                <flux:label>Content Type</flux:label>
                                <flux:select wire:model="scheduledItems.{{ $scheduledItemIndex }}.media_type" :disabled="! $this->isEditable()" required>
                                    @foreach ($mediaTypes as $mediaType)
                                        <option value="{{ $mediaType->value }}">{{ Str::of($mediaType->value)->headline() }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            <flux:field>
                                <flux:label>Instagram Account</flux:label>
                                <flux:select wire:model="scheduledItems.{{ $scheduledItemIndex }}.instagram_account_id" :disabled="! $this->isEditable()" required>
                                    <option value="">Select account</option>
                                    @foreach ($instagramAccounts as $instagramAccount)
                                        <option value="{{ $instagramAccount->id }}">{{ $instagramAccount->username }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>

                            <flux:field>
                                <flux:label>Scheduled At</flux:label>
                                <flux:input type="datetime-local" wire:model="scheduledItems.{{ $scheduledItemIndex }}.scheduled_at" :disabled="! $this->isEditable()" required />
                            </flux:field>
                        </div>

                        <flux:field class="mt-4">
                            <flux:label>Description</flux:label>
                            <flux:textarea wire:model="scheduledItems.{{ $scheduledItemIndex }}.description" rows="3" :disabled="! $this->isEditable()" />
                        </flux:field>

                        @if ($this->isEditable())
                            <div class="mt-4 flex justify-end">
                                <flux:button type="button" variant="danger" wire:click="removeScheduledItem({{ $scheduledItemIndex }})">
                                    Remove Scheduled Post
                                </flux:button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button type="button" variant="danger" wire:click="confirmDelete">
                Delete
            </flux:button>

            <div class="flex items-center gap-3">
                @if ($currentStep > 1)
                    <flux:button type="button" variant="filled" wire:click="previousStep">
                        Previous
                    </flux:button>
                @endif

                @if ($currentStep < 3)
                    <flux:button type="button" variant="primary" wire:click="nextStep">
                        Next
                    </flux:button>
                @elseif ($this->isEditable())
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
