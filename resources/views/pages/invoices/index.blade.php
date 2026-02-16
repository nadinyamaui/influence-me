@php
    use App\Enums\InvoiceStatus;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Invoices</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Track payment status and follow upcoming due dates.</p>
        </div>

        <flux:button href="#" variant="primary" title="New Invoice" aria-label="New Invoice">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
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

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Outstanding</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">${{ number_format($summary['total_outstanding'], 2) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Sent + overdue invoices</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Paid This Month</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">${{ number_format($summary['paid_this_month'], 2) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Collected in {{ now()->format('F') }}</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Overdue</p>
            <p class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-300">{{ number_format($summary['overdue_count']) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Invoices past due date</p>
        </article>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model.live="status" :label="__('Status')">
                @foreach (InvoiceStatus::filters() as $filterStatus)
                    <option value="{{ $filterStatus }}">
                        {{ $filterStatus === 'all' ? 'All' : InvoiceStatus::from($filterStatus)->label() }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="client" :label="__('Client')">
                <option value="all">All</option>
                @foreach ($clients as $clientOption)
                    <option value="{{ $clientOption->id }}">{{ $clientOption->name }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($invoices->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No invoices yet. Create your first invoice.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Invoice #</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Total</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Due Date</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($invoices as $invoice)
                            @php
                                $isOverdue = $invoice->status === InvoiceStatus::Overdue || (
                                    $invoice->due_date->isPast() && in_array($invoice->status, [InvoiceStatus::Draft, InvoiceStatus::Sent], true)
                                );
                            @endphp
                            <tr wire:key="invoice-row-{{ $invoice->id }}">
                                <td class="px-4 py-3">
                                    <a
                                        href="{{ url('/invoices/'.$invoice->id) }}"
                                        class="font-medium text-zinc-900 underline-offset-2 hover:underline dark:text-zinc-100"
                                    >
                                        INV-{{ $invoice->created_at->format('Y') }}-{{ str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $invoice->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $invoice->status->badgeClasses() }}">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">${{ number_format((float) $invoice->total, 2) }}</td>
                                <td class="px-4 py-3 {{ $isOverdue ? 'text-rose-600 dark:text-rose-300' : 'text-zinc-700 dark:text-zinc-200' }}">{{ $invoice->due_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('/invoices/'.$invoice->id) }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            title="View"
                                            aria-label="View"
                                        >
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        </a>

                                        @if ($invoice->status === InvoiceStatus::Draft)
                                            <a
                                                href="{{ url('/invoices/'.$invoice->id.'/edit') }}"
                                                class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                                title="Edit"
                                                aria-label="Edit"
                                            >
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            </a>
                                            <button
                                                type="button"
                                                wire:click="delete({{ $invoice->id }})"
                                                wire:confirm="Are you sure you want to delete invoice {{ $invoice->invoice_number }}?"
                                                class="inline-flex items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950/40"
                                                title="Delete"
                                                aria-label="Delete"
                                            >
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $invoices->links() }}
            </div>
        </section>
    @endif

</div>
