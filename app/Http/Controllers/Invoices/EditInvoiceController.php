<?php

namespace App\Http\Controllers\Invoices;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EditInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice): View|RedirectResponse
    {
        Gate::authorize('view', $invoice);

        if ($invoice->status !== InvoiceStatus::Draft) {
            return redirect()->route('invoices.show', $invoice);
        }

        Gate::authorize('update', $invoice);

        return view('pages.invoices.edit', [
            'invoice' => $invoice,
        ]);
    }
}
