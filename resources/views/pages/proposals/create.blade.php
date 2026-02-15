<div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">New Proposal</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Create a proposal with campaigns and scheduled content for your client.</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="form.title" name="title" placeholder="Proposal title" required />
                <flux:error name="form.title" />
            </flux:field>

            <flux:field>
                <flux:label>Client</flux:label>
                <flux:select wire:model="form.client_id" name="client_id" required>
                    <option value="">Select a client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="form.client_id" />
            </flux:field>

            <flux:field>
                <div class="flex items-center justify-between">
                    <flux:label>Content</flux:label>
                    <flux:button type="button" size="xs" wire:click="togglePreview">
                        {{ $previewing ? 'Edit' : 'Preview' }}
                    </flux:button>
                </div>
                @if ($previewing)
                    <div class="prose prose-sm dark:prose-invert max-w-none rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        {!! Str::markdown($this->form->content ?: '*Nothing to preview*') !!}
                    </div>
                @else
                    <flux:textarea wire:model="form.content" name="content" rows="10" placeholder="Write your proposal content here..." required />
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Supports Markdown formatting</p>
                @endif
                <flux:error name="form.content" />
            </flux:field>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @include('pages.proposals._campaign-builder')
        </section>

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                Save as Draft
            </flux:button>
        </div>
    </form>
</div>
