<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Create Proposal</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Step 1 of 4: Choose the client and title, then continue to proposal details.</p>
        </div>

        <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
            Cancel
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-5 md:grid-cols-2">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="title" required />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>Client</flux:label>
                <flux:select wire:model="client_id" required>
                    <option value="">Select a client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="client_id" />
            </flux:field>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                Save and Continue
            </flux:button>
        </div>
    </form>
</div>
