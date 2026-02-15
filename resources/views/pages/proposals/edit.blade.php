<div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Edit Proposal</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            @if ($editable)
                Update your proposal details, campaigns, and scheduled content.
            @else
                This proposal is {{ $proposal->status->value }} and cannot be edited.
            @endif
        </p>
    </div>

    @if ($editable)
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

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:button type="button" variant="danger" wire:click="confirmDelete" title="Delete Proposal" aria-label="Delete Proposal">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                </flux:button>

                <div class="flex items-center gap-3">
                    <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Save
                    </flux:button>
                </div>
            </div>
        </form>
    @else
        <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h2 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Title</h2>
                <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $proposal->title }}</p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Client</h2>
                <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $proposal->client?->name ?? '—' }}</p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Content</h2>
                <div class="prose prose-sm dark:prose-invert mt-1 max-w-none">
                    {!! Str::markdown($proposal->content) !!}
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button type="button" variant="danger" wire:click="confirmDelete" title="Delete Proposal" aria-label="Delete Proposal">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </flux:button>

            <div class="flex items-center gap-3">
                <flux:button :href="route('proposals.index')" variant="filled" wire:navigate>
                    Back
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="duplicate">
                    Duplicate
                </flux:button>
            </div>
        </div>
    @endif

    <flux:modal
        name="proposal-edit-delete-modal"
        wire:model="confirmingDelete"
        @close="cancelDelete"
        class="max-w-lg"
    >
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete this proposal?</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Are you sure? This will also delete all linked campaigns and scheduled content.</p>

        <div class="mt-5 flex justify-end gap-2">
            <flux:button type="button" variant="filled" wire:click="cancelDelete">
                Cancel
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="delete" title="Delete Proposal" aria-label="Delete Proposal">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </flux:button>
        </div>
    </flux:modal>
</div>
