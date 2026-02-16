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
            <flux:input wire:model="form.name" name="name" placeholder="Jordan Smith" required />
            <flux:error name="form.name" />
        </flux:field>

        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="form.email" name="email" type="email" placeholder="client@example.com" />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <flux:label>Company Name</flux:label>
            <flux:input wire:model="form.company_name" name="company_name" placeholder="Acme Co." />
            <flux:error name="form.company_name" />
        </flux:field>

        <flux:field>
            <flux:label>Client Type</flux:label>
            <flux:select wire:model="form.type" name="type">
                @foreach (ClientType::cases() as $typeOption)
                    <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                @endforeach
            </flux:select>
            <flux:error name="form.type" />
        </flux:field>

        <flux:field>
            <flux:label>Phone</flux:label>
            <flux:input wire:model="form.phone" name="phone" placeholder="(555) 123-1234" />
            <flux:error name="form.phone" />
        </flux:field>

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="form.notes" name="notes" rows="6" placeholder="Add client context, preferences, and campaign notes." />
            <flux:error name="form.notes" />
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
