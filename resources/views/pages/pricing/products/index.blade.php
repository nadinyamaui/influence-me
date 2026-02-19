<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Pricing Products</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage reusable deliverables and package pricing by platform.</p>
        </div>

        <flux:button :href="route('pricing.products.create')" variant="primary" wire:navigate title="Add Product" aria-label="Add Product">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Search by product name')"
            />

            <flux:select wire:model.live="status" :label="__('Status')">
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="platform" :label="__('Platform')">
                <option value="all">All Platforms</option>
                @foreach ($platformOptions as $platformOption)
                    <option value="{{ $platformOption->value }}">{{ $platformOption->label() }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($products->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No pricing products found for the selected filters.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:table :paginate="$products">
                <flux:table.columns>
                    <flux:table.column
                        sortable
                        :sorted="$this->isSortedBy('name')"
                        :direction="$this->sortDirection('name')"
                        wire:click="sortBy('name')"
                    >
                        Name
                    </flux:table.column>
                    <flux:table.column>Platform</flux:table.column>
                    <flux:table.column>Media Type</flux:table.column>
                    <flux:table.column>Billing Unit</flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$this->isSortedBy('price')"
                        :direction="$this->sortDirection('price')"
                        wire:click="sortBy('price')"
                    >
                        Price
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$this->isSortedBy('created_at')"
                        :direction="$this->sortDirection('created_at')"
                        wire:click="sortBy('created_at')"
                    >
                        Created
                    </flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($products as $product)
                        <flux:table.row :key="'catalog-product-'.$product->id">
                            <flux:table.cell variant="strong">{{ $product->name }}</flux:table.cell>
                            <flux:table.cell>{{ $product->platform->label() }}</flux:table.cell>
                            <flux:table.cell>{{ $product->media_type?->label() ?? 'Generic' }}</flux:table.cell>
                            <flux:table.cell>{{ $product->billing_unit->label() }}</flux:table.cell>
                            <flux:table.cell>{{ $product->currency }} {{ number_format((float) $product->base_price, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $product->created_at->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($product->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Archived</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('pricing.products.edit', $product) }}"
                                        class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        title="Edit"
                                        aria-label="Edit"
                                        wire:navigate
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </a>

                                    @if ($product->is_active)
                                        <button
                                            type="button"
                                            wire:click="archive({{ $product->id }})"
                                            wire:confirm="Archive '{{ $product->name }}'?"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            title="Archive"
                                            aria-label="Archive"
                                        >
                                            <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="unarchive({{ $product->id }})"
                                            wire:confirm="Unarchive '{{ $product->name }}'?"
                                            class="inline-flex items-center rounded-md border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-200 dark:hover:bg-emerald-950/40"
                                            title="Unarchive"
                                            aria-label="Unarchive"
                                        >
                                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </section>
    @endif
</div>
