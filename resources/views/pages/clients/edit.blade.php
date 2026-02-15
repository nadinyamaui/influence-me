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
            <flux:input wire:model="form.name" name="name" required />
            <flux:error name="form.name" />
        </flux:field>

        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="form.email" name="email" type="email" />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <flux:label>Company Name</flux:label>
            <flux:input wire:model="form.company_name" name="company_name" />
            <flux:error name="form.company_name" />
        </flux:field>

        <flux:field>
            <flux:label>Client Type</flux:label>
            <flux:select wire:model="form.type" name="type">
                <option value="{{ ClientType::Brand->value }}">Brand</option>
                <option value="{{ ClientType::Individual->value }}">Individual</option>
            </flux:select>
            <flux:error name="form.type" />
        </flux:field>

        <flux:field>
            <flux:label>Phone</flux:label>
            <flux:input wire:model="form.phone" name="phone" />
            <flux:error name="form.phone" />
        </flux:field>

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="form.notes" name="notes" rows="6" />
            <flux:error name="form.notes" />
        </flux:field>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button type="button" variant="danger" wire:click="confirmDelete" title="Delete Client" aria-label="Delete Client">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
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

    <flux:modal
        name="client-edit-delete-modal"
        wire:model="confirmingDelete"
        @close="cancelDelete"
        class="max-w-lg"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete this client?</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Are you sure? This will also delete all proposals and invoices for this client.</p>

        <div class="mt-5 flex justify-end gap-2">
            <flux:button type="button" variant="filled" wire:click="cancelDelete">
                Cancel
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="delete" title="Delete Client" aria-label="Delete Client">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </flux:button>
        </div>
    </flux:modal>
</div>
