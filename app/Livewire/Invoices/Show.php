<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);

        $this->invoice = $invoice->load([
            'user:id,name,email',
            'client:id,name,company_name,email',
            'items:id,invoice_id,description,quantity,unit_price,total',
        ]);
    }

    public function delete()
    {
        $this->authorize('delete', $this->invoice);

        $invoiceNumber = $this->invoice->invoice_number;
        $this->invoice->delete();

        session()->flash('status', 'Invoice '.$invoiceNumber.' deleted.');

        return $this->redirectRoute('invoices.index', navigate: true);
    }

    public function render()
    {
        return view('pages.invoices.show', [
            'isDraft' => $this->invoice->status === InvoiceStatus::Draft,
            'isSent' => $this->invoice->status === InvoiceStatus::Sent,
            'isPaid' => $this->invoice->status === InvoiceStatus::Paid,
            'isOverdue' => $this->invoice->status === InvoiceStatus::Overdue,
        ])->layout('layouts.app', [
            'title' => __('Invoice Preview'),
        ]);
    }
}
