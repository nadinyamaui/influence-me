@php
    use App\Enums\ClientType;
@endphp

<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Edit Client</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Update client details and relationship metadata.</p>
    </div>

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <flux:field>
            <flux:label>Client Name</flux:label>
            <flux:input wire:model="name" name="name" required />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="email" name="email" type="email" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>Company Name</flux:label>
            <flux:input wire:model="company_name" name="company_name" />
            <flux:error name="company_name" />
        </flux:field>

        <flux:field>
            <flux:label>Client Type</flux:label>
            <flux:select wire:model="type" name="type">
                <option value="{{ ClientType::Brand->value }}">Brand</option>
                <option value="{{ ClientType::Individual->value }}">Individual</option>
            </flux:select>
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>Phone</flux:label>
            <flux:input wire:model="phone" name="phone" />
            <flux:error name="phone" />
        </flux:field>

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="notes" name="notes" rows="6" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button type="button" variant="danger" wire:click="confirmDelete">
                Delete
            </flux:button>

            <div class="flex items-center gap-3">
                <flux:button :href="route('clients.index')" variant="filled" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/60 p-4">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete this client?</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Are you sure? This will also delete all proposals and invoices for this client.</p>

                <div class="mt-5 flex justify-end gap-2">
                    <flux:button type="button" variant="filled" wire:click="cancelDelete">
                        Cancel
                    </flux:button>
                    <flux:button type="button" variant="danger" wire:click="delete">
                        Delete Client
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
