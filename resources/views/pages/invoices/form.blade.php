<div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $isEditing ? 'Edit Invoice' : 'Create Invoice' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $isEditing ? 'Update invoice details and line items.' : 'Build an invoice with mixed product, plan, and custom line items.' }}
            </p>
        </div>

        <flux:button :href="route('invoices.index')" variant="filled" wire:navigate>
            {{ $isEditing ? 'Back' : 'Cancel' }}
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-5 md:grid-cols-2">
            <flux:field>
                <flux:label>Client</flux:label>
                <flux:select wire:model="client_id" name="client_id" required>
                    <option value="">Select a client</option>
                    @foreach ($clients as $clientOption)
                        <option value="{{ $clientOption->id }}">{{ $clientOption->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="client_id" />
            </flux:field>

            <flux:field>
                <flux:label>Due Date</flux:label>
                <flux:input wire:model="due_date" name="due_date" type="date" required />
                <flux:error name="due_date" />
            </flux:field>

            <flux:field>
                <flux:label>Tax Rate</flux:label>
                <flux:input wire:model.live.debounce.200ms="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" suffix="%" />
                <flux:error name="tax_rate" />
            </flux:field>

            <flux:field class="md:col-span-2">
                <flux:label>Notes</flux:label>
                <flux:textarea wire:model="notes" name="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </div>

        <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Line Items</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Select a product/plan source or keep it custom.</p>
                </div>

                <flux:button type="button" variant="ghost" wire:click="addItemRow" title="Add Item" aria-label="Add Item">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                </flux:button>
            </div>

            <flux:error name="items" />

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Source</th>
                            <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Description</th>
                            <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Qty</th>
                            <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Unit Price</th>
                            <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Line Total</th>
                            <th class="px-3 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($items as $index => $item)
                            @php
                                $quantity = (float) ($item['quantity'] ?? 0);
                                $unitPrice = (float) ($item['unit_price'] ?? 0);
                            @endphp
                            <tr wire:key="invoice-item-row-{{ $index }}" class="align-top">
                                <td class="min-w-72 px-3 py-3">
                                    <flux:field>
                                        <flux:label class="sr-only">Source</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.source" name="items.{{ $index }}.source">
                                            <option value="">Custom</option>
                                            @foreach ($products as $product)
                                                <option value="product:{{ $product->id }}">
                                                    Product: {{ $product->name }} ({{ $product->currency }} {{ number_format((float) $product->base_price, 2) }})
                                                </option>
                                            @endforeach
                                            @foreach ($plans as $plan)
                                                <option value="plan:{{ $plan->id }}">
                                                    Plan: {{ $plan->name }} ({{ $plan->currency }} {{ number_format((float) ($plan->bundle_price ?? 0), 2) }})
                                                </option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </td>
                                <td class="min-w-72 px-3 py-3">
                                    <flux:field>
                                        <flux:label class="sr-only">Description</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.description" name="items.{{ $index }}.description" required />
                                        <flux:error name="items.{{ $index }}.description" />
                                    </flux:field>
                                </td>
                                <td class="min-w-28 px-3 py-3">
                                    <flux:field>
                                        <flux:label class="sr-only">Quantity</flux:label>
                                        <flux:input wire:model.live.debounce.200ms="items.{{ $index }}.quantity" name="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" required />
                                        <flux:error name="items.{{ $index }}.quantity" />
                                    </flux:field>
                                </td>
                                <td class="min-w-36 px-3 py-3">
                                    <flux:field>
                                        <flux:label class="sr-only">Unit Price</flux:label>
                                        <flux:input wire:model.live.debounce.200ms="items.{{ $index }}.unit_price" name="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" prefix="$" required />
                                        <flux:error name="items.{{ $index }}.unit_price" />
                                    </flux:field>
                                </td>
                                <td class="px-3 py-3 text-zinc-700 dark:text-zinc-200">
                                    ${{ number_format($quantity * $unitPrice, 2) }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex justify-end">
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            wire:click="removeItemRow({{ $index }})"
                                            wire:confirm="Remove this line item?"
                                            title="Remove Item"
                                            aria-label="Remove Item"
                                        >
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <section class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Totals</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-zinc-600 dark:text-zinc-300">Subtotal</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($this->subtotal, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-zinc-600 dark:text-zinc-300">Tax ({{ number_format((float) ($tax_rate !== '' ? $tax_rate : 0), 2) }}%)</dt>
                    <dd class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($this->taxAmount, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-t border-zinc-200 pt-2 text-base dark:border-zinc-700">
                    <dt class="font-semibold text-zinc-900 dark:text-zinc-100">Total</dt>
                    <dd class="font-semibold text-zinc-900 dark:text-zinc-100">${{ number_format($this->totalAmount, 2) }}</dd>
                </div>
            </dl>
        </section>

        <div class="flex items-center justify-end gap-3">
            <flux:button :href="route('invoices.index')" variant="filled" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ $isEditing ? 'Save Changes' : 'Save Invoice' }}
            </flux:button>
        </div>
    </form>
</div>
