@php
    use App\Enums\MediaType;
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Campaigns</h2>
        <flux:button type="button" size="sm" wire:click="addCampaign">
            <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i> Add Campaign
        </flux:button>
    </div>

    @foreach ($this->form->campaigns as $cIndex => $campaign)
        <div wire:key="campaign-{{ $cIndex }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Campaign {{ $cIndex + 1 }}</h3>
                @if (count($this->form->campaigns) > 1)
                    <flux:button type="button" size="xs" variant="danger" wire:click="removeCampaign({{ $cIndex }})" title="Remove campaign" aria-label="Remove campaign">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </flux:button>
                @endif
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Campaign Name</flux:label>
                    <flux:input wire:model="form.campaigns.{{ $cIndex }}.name" placeholder="e.g. Summer Launch" required />
                    <flux:error name="form.campaigns.{{ $cIndex }}.name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="form.campaigns.{{ $cIndex }}.description" rows="2" placeholder="Optional campaign description" />
                    <flux:error name="form.campaigns.{{ $cIndex }}.description" />
                </flux:field>

                <div class="mt-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Scheduled Content</h4>
                        <flux:button type="button" size="xs" wire:click="addScheduledItem({{ $cIndex }})">
                            <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i> Add Content
                        </flux:button>
                    </div>

                    <div class="space-y-4">
                        @foreach ($campaign['scheduled_items'] as $sIndex => $item)
                            <div wire:key="campaign-{{ $cIndex }}-item-{{ $sIndex }}" class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-600 dark:bg-zinc-900">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Content {{ $sIndex + 1 }}</span>
                                    @if (count($campaign['scheduled_items']) > 1)
                                        <flux:button type="button" size="xs" variant="danger" wire:click="removeScheduledItem({{ $cIndex }}, {{ $sIndex }})" title="Remove content item" aria-label="Remove content item">
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                        </flux:button>
                                    @endif
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <flux:field class="sm:col-span-2">
                                        <flux:label>Title</flux:label>
                                        <flux:input wire:model="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.title" placeholder="Content title" required />
                                        <flux:error name="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.title" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>Content Type</flux:label>
                                        <flux:select wire:model="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.media_type" required>
                                            <option value="{{ MediaType::Post->value }}">Post</option>
                                            <option value="{{ MediaType::Reel->value }}">Reel</option>
                                            <option value="{{ MediaType::Story->value }}">Story</option>
                                        </flux:select>
                                        <flux:error name="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.media_type" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>Instagram Account</flux:label>
                                        <flux:select wire:model="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.instagram_account_id" required>
                                            <option value="">Select account</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->username }}</option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.instagram_account_id" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>Scheduled Date &amp; Time</flux:label>
                                        <flux:input type="datetime-local" wire:model="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.scheduled_at" required />
                                        <flux:error name="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.scheduled_at" />
                                    </flux:field>

                                    <flux:field class="sm:col-span-2">
                                        <flux:label>Description</flux:label>
                                        <flux:textarea wire:model="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.description" rows="2" placeholder="Optional content description" />
                                        <flux:error name="form.campaigns.{{ $cIndex }}.scheduled_items.{{ $sIndex }}.description" />
                                    </flux:field>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
