<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $isEditMode ? 'Edit Tax Rate' : 'Create Tax Rate' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $isEditMode
                    ? 'Update tax label, percentage, and active state.'
                    : 'Add a reusable tax percentage for commercial workflows.' }}
            </p>
        </div>

        <flux:button :href="route('pricing.tax-rates.index')" variant="filled" wire:navigate>
            {{ $isEditMode ? 'Back' : 'Cancel' }}
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <flux:field>
            <flux:label>Label</flux:label>
            <flux:input wire:model="label" name="label" placeholder="VAT" required />
            <flux:error name="label" />
        </flux:field>

        <flux:field>
            <flux:label>Rate (%)</flux:label>
            <flux:input wire:model="rate" name="rate" type="number" step="0.01" min="0" max="100" placeholder="20.00" required />
            <flux:error name="rate" />
        </flux:field>

        <flux:field variant="inline">
            <flux:checkbox wire:model="is_active" name="is_active" />
            <flux:label>Active tax rate</flux:label>
            <flux:error name="is_active" />
        </flux:field>

        <div class="flex flex-wrap items-center justify-between gap-3">
            @if ($isEditMode)
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="delete"
                    wire:confirm="Delete this tax rate?"
                    class="text-rose-700 dark:text-rose-200"
                >
                    Delete
                </flux:button>
            @endif

            <div @class(['flex items-center gap-3', 'ml-auto' => ! $isEditMode])>
                <flux:button :href="route('pricing.tax-rates.index')" variant="filled" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $isEditMode ? 'Save Changes' : 'Save Tax Rate' }}
                </flux:button>
            </div>
        </div>
    </form>
</div>
