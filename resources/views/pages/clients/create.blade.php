@php
    use App\Enums\ClientType;
@endphp

<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Add Client</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Create a new client profile to manage campaigns, proposals, and invoices.</p>
    </div>

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <flux:field>
            <flux:label>Client Name</flux:label>
            <flux:input wire:model="name" name="name" placeholder="Jordan Smith" required />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="email" name="email" type="email" placeholder="client@example.com" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>Company Name</flux:label>
            <flux:input wire:model="company_name" name="company_name" placeholder="Acme Co." />
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
            <flux:input wire:model="phone" name="phone" placeholder="(555) 123-1234" />
            <flux:error name="phone" />
        </flux:field>

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="notes" name="notes" rows="6" placeholder="Add client context, preferences, and campaign notes." />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('clients.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                Save
            </flux:button>
        </div>
    </form>
</div>
