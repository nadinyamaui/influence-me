@php
    use App\Enums\InvoiceStatus;
@endphp

<div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('send'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('send') }}
        </div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Invoice #{{ $invoice->invoice_number }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $invoice->status->badgeClasses() }}">{{ $invoice->status->label() }}</span>
                <span>Issued {{ $invoice->created_at->format('M j, Y') }}</span>
                <span>Due {{ $invoice->due_date->format('M j, Y') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:button :href="route('invoices.index')" variant="filled" wire:navigate>
                Back
            </flux:button>

            @if ($invoice->status === InvoiceStatus::Draft)
                <flux:button :href="route('invoices.edit', $invoice)" variant="filled" wire:navigate>
                    Edit
                </flux:button>
            @endif

            @if ($this->canSend())
                <flux:button
                    type="button"
                    variant="primary"
                    wire:click="send"
                    wire:confirm="Send invoice #{{ $invoice->invoice_number }} (${{ number_format((float) $invoice->total, 2) }}) to {{ $invoice->client?->name ?? 'this client' }} at {{ $invoice->client?->email ?? 'no email' }}?"
                >
                    Send to Client
                </flux:button>
            @elseif ($this->canResend())
                <flux:button
                    type="button"
                    variant="primary"
                    wire:click="resend"
                    wire:confirm="Resend invoice #{{ $invoice->invoice_number }} (${{ number_format((float) $invoice->total, 2) }}) to {{ $invoice->client?->name ?? 'this client' }} at {{ $invoice->client?->email ?? 'no email' }}?"
                >
                    Resend
                </flux:button>
            @endif
        </div>
    </div>

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">From</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->user?->name ?? '—' }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $invoice->user?->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">To</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->client?->name ?? '—' }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $invoice->client?->company_name ?? '—' }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $invoice->client?->email ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-300">Description</th>
                        <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Qty</th>
                        <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Price</th>
                        <th class="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-300">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($invoice->items as $item)
                        <tr>
                            <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">{{ $item->description }}</td>
                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-200">${{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-zinc-600 dark:text-zinc-300">No line items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <dl class="mt-6 ml-auto grid w-full max-w-xs gap-2 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-zinc-600 dark:text-zinc-300">Subtotal</dt>
                <dd class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format((float) $invoice->subtotal, 2) }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-zinc-600 dark:text-zinc-300">Tax ({{ number_format((float) $invoice->tax_rate, 2) }}%)</dt>
                <dd class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format((float) $invoice->tax_amount, 2) }}</dd>
            </div>
            <div class="flex items-center justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                <dt class="text-zinc-700 dark:text-zinc-200">Total</dt>
                <dd class="text-base font-semibold text-zinc-900 dark:text-zinc-100">${{ number_format((float) $invoice->total, 2) }}</dd>
            </div>
        </dl>

        @if (filled($invoice->notes))
            <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">
                {{ $invoice->notes }}
            </div>
        @endif
    </section>
</div>
