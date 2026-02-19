<div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Edit Pricing Plan</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Update pricing bundle details and line-item composition.</p>
        </div>

        <flux:button :href="route('pricing.plans.index')" variant="filled" wire:navigate>
            Back
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-5 md:grid-cols-2">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" name="name" required />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Currency</flux:label>
                <flux:input wire:model="currency" name="currency" maxlength="3" required />
                <flux:error name="currency" />
            </flux:field>

            <flux:field class="md:col-span-2">
                <flux:label>Description</flux:label>
                <flux:textarea wire:model="description" name="description" rows="3" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>Bundle Price (Optional)</flux:label>
                <flux:input wire:model="bundle_price" name="bundle_price" type="number" step="0.01" min="0" />
                <flux:error name="bundle_price" />
            </flux:field>

            <flux:field variant="inline" class="self-end">
                <flux:checkbox wire:model="is_active" name="is_active" />
                <flux:label>Active plan</flux:label>
                <flux:error name="is_active" />
            </flux:field>
        </div>

        @include('pages.pricing.plans.partials.items-table', [
            'itemsDescription' => 'Adjust products, quantities, and optional unit overrides.',
        ])

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('pricing.plans.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                Save Changes
            </flux:button>
        </div>
    </form>
</div>
