<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $status = 'all';

    public string $client = 'all';

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, InvoiceStatus::filters(), true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    public function updatedClient(): void
    {
        if ($this->client !== 'all' && ! is_numeric($this->client)) {
            $this->client = 'all';
        }

        $this->resetPage();
    }

    public function delete(int $invoiceId): void
    {
        $invoice = User::resolveInvoice($invoiceId);
        $this->authorize('delete', $invoice);

        $this->resetErrorBag('delete');

        $invoice->delete();

        session()->flash('status', 'Invoice deleted.');
    }

    public function render()
    {
        return view('pages.invoices.index', [
            'invoices' => $this->invoices(),
            'clients' => User::availableClients(),
            'summary' => $this->summary(),
        ])->layout('layouts.app', [
            'title' => __('Invoices'),
        ]);
    }

    private function invoices(): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->forUser()
            ->with('client')
            ->filterByStatus($this->status)
            ->latestFirst();

        if ($this->client !== 'all' && is_numeric($this->client)) {
            $query->forClient((int) $this->client);
        }

        return $query->paginate(10);
    }

    private function summary(): array
    {
        $invoiceQuery = Invoice::query()->forUser();

        return [
            'total_outstanding' => (float) (clone $invoiceQuery)
                ->pending()
                ->sum('total'),
            'paid_this_month' => (float) (clone $invoiceQuery)
                ->paidInMonth(now())
                ->sum('total'),
            'overdue_count' => (clone $invoiceQuery)
                ->overdue()
                ->count(),
        ];
    }
}
