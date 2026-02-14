@php
    use App\Enums\ClientType;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Clients</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage your client relationships and campaign assignments.</p>
        </div>

        <flux:button :href="route('clients.create')" variant="primary" wire:navigate>
            Add Client
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('delete'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('delete') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Search by name, email, or company')"
            />

            <flux:select wire:model.live="type" :label="__('Type')">
                <option value="all">All</option>
                <option value="{{ ClientType::Brand->value }}">Brand</option>
                <option value="{{ ClientType::Individual->value }}">Individual</option>
            </flux:select>
        </div>
    </section>

    @if ($clients->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No clients yet. Add your first client to start managing campaigns.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Company</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Type</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Campaigns</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($clients as $client)
                            <tr wire:key="client-row-{{ $client->id }}">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $client->name }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $client->company_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($client->type === ClientType::Brand)
                                        <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">Brand</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Individual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $client->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ number_format($client->instagram_media_count) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('/clients/'.$client->id) }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            wire:navigate
                                        >
                                            View
                                        </a>
                                        <a
                                            href="{{ url('/clients/'.$client->id.'/edit') }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            wire:navigate
                                        >
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $client->id }})"
                                            class="inline-flex items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950/40"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $clients->links() }}
            </div>
        </section>
    @endif

    @if ($deletingClientId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/60 p-4">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete client?</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                    You are about to delete
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->deletingClient()?->name ?? 'this client' }}</span>.
                </p>

                <div class="mt-5 flex justify-end gap-2">
                    <flux:button type="button" variant="filled" wire:click="cancelDelete">
                        Cancel
                    </flux:button>
                    <flux:button type="button" variant="danger" wire:click="delete">
                        Delete
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
