@php
    use App\Enums\InvoiceStatus;
    use App\Enums\ProposalStatus;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Welcome back, {{ $client->name }}</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
            @if (filled($influencerName))
                You are collaborating with {{ $influencerName }}.
            @else
                Review your latest proposals, invoices, and content performance.
            @endif
        </p>
    </div>

    <section class="grid gap-4 md:grid-cols-2">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Active Proposals</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['active_proposals']) }}</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Pending Invoices</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['pending_invoice_count']) }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">${{ number_format($summary['pending_invoice_total'], 2) }}</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Linked Content</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['linked_content_count']) }}</p>
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Total Reach</p>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_reach']) }}</p>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Recent Proposals</h2>
            </div>

            @if ($recentProposals->isEmpty())
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No proposals yet.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($recentProposals as $proposal)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                            <a href="{{ url('/portal/proposals/'.$proposal->id) }}" class="truncate text-sm font-medium text-zinc-900 hover:underline dark:text-zinc-100">
                                {{ $proposal->title }}
                            </a>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                @if ($proposal->status === ProposalStatus::Sent)
                                    bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200
                                @elseif ($proposal->status === ProposalStatus::Approved)
                                    bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200
                                @elseif ($proposal->status === ProposalStatus::Rejected)
                                    bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200
                                @elseif ($proposal->status === ProposalStatus::Revised)
                                    bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200
                                @else
                                    bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200
                                @endif
                            ">
                                {{ ucfirst($proposal->status->value) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-300">Recent Invoices</h2>
            </div>

            @if ($recentInvoices->isEmpty())
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No invoices yet.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($recentInvoices as $invoice)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                            <a href="{{ url('/portal/invoices/'.$invoice->id) }}" class="truncate text-sm font-medium text-zinc-900 hover:underline dark:text-zinc-100">
                                Invoice #{{ $invoice->invoice_number }}
                            </a>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">${{ number_format((float) $invoice->total, 2) }}</span>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                    @if ($invoice->status === InvoiceStatus::Sent)
                                        bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200
                                    @elseif ($invoice->status === InvoiceStatus::Paid)
                                        bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200
                                    @elseif ($invoice->status === InvoiceStatus::Overdue)
                                        bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200
                                    @else
                                        bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200
                                    @endif
                                ">
                                    {{ ucfirst($invoice->status->value) }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>
</div>
