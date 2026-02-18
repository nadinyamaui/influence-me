<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Create Pricing Product</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Add a reusable pricing item for proposals and invoicing workflows.</p>
        </div>

        <flux:button :href="route('pricing.products.index')" variant="filled" wire:navigate>
            Cancel
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model="name" name="name" placeholder="Instagram Reel Deliverable" required />
            <flux:error name="name" />
        </flux:field>

        <div class="grid gap-5 md:grid-cols-2">
            <flux:field>
                <flux:label>Platform</flux:label>
                <flux:select wire:model="platform" name="platform" required>
                    @foreach ($platforms as $platformOption)
                        <option value="{{ $platformOption->value }}">{{ $platformOption->label() }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="platform" />
            </flux:field>

            <flux:field>
                <flux:label>Media Type</flux:label>
                <flux:select wire:model="media_type" name="media_type">
                    <option value="">Generic</option>
                    @foreach ($mediaTypes as $mediaType)
                        <option value="{{ $mediaType->value }}">{{ $mediaType->label() }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="media_type" />
            </flux:field>

            <flux:field>
                <flux:label>Billing Unit</flux:label>
                <flux:select wire:model="billing_unit" name="billing_unit" required>
                    @foreach ($billingUnits as $billingUnit)
                        <option value="{{ $billingUnit->value }}">{{ $billingUnit->label() }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="billing_unit" />
            </flux:field>

            <flux:field>
                <flux:label>Base Price</flux:label>
                <flux:input wire:model="base_price" name="base_price" type="number" step="0.01" min="0" placeholder="500.00" required />
                <flux:error name="base_price" />
            </flux:field>

            <flux:field>
                <flux:label>Currency</flux:label>
                <flux:input wire:model="currency" name="currency" maxlength="3" placeholder="USD" required />
                <flux:error name="currency" />
            </flux:field>
        </div>

        <flux:field variant="inline">
            <flux:checkbox wire:model="is_active" name="is_active" />
            <flux:label>Active product</flux:label>
            <flux:error name="is_active" />
        </flux:field>

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('pricing.products.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                Save Product
            </flux:button>
        </div>
    </form>
</div>
