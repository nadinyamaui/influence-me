<?php

namespace App\Livewire\Invoices;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\CatalogPlan;
use App\Models\CatalogProduct;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?int $invoiceId = null;

    public string $client_id = '';

    public string $due_date = '';

    public string $tax_rate = '0';

    public string $notes = '';

    public array $items = [];

    public function mount($invoice = null): void
    {
        if ($invoice === null) {
            $this->authorize('create', Invoice::class);
            $this->due_date = now()->addDays(7)->toDateString();
            $this->items = [$this->emptyItem()];

            return;
        }

        $resolvedInvoice = $invoice instanceof Invoice
            ? $invoice
            : Invoice::query()->findOrFail((int) $invoice);

        $this->authorize('update', $resolvedInvoice);

        $this->invoiceId = $resolvedInvoice->id;
        $this->fillFromInvoice($resolvedInvoice);
    }

    protected function rules(): array
    {
        return StoreInvoiceRequest::initialRules((int) auth()->id());
    }

    public function addItemRow(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItemRow(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        if (! str_ends_with($key, '.source')) {
            return;
        }

        $segments = explode('.', $key);
        $index = isset($segments[0]) ? (int) $segments[0] : -1;

        if ($index < 0 || ! isset($this->items[$index])) {
            return;
        }

        $this->syncItemFromSource($index, (string) $value, app(InvoiceService::class));
    }

    public function save(InvoiceService $invoiceService)
    {
        $this->items = $this->normalizedItems();

        $validated = $this->validate();
        $validated['items'] = $this->normalizedValidatedItems($validated['items'] ?? []);

        if (! $this->validateExclusiveSources($validated['items'])) {
            return null;
        }

        if ($this->invoiceId !== null) {
            $invoice = Invoice::query()->findOrFail($this->invoiceId);
            $this->authorize('update', $invoice);

            $invoiceService->update(auth()->user(), $invoice, $validated);
            session()->flash('status', 'Invoice updated successfully.');

            return $this->redirectRoute('invoices.index', navigate: true);
        }

        $this->authorize('create', Invoice::class);
        $invoiceService->create(auth()->user(), $validated);
        session()->flash('status', 'Invoice created successfully.');

        return $this->redirectRoute('invoices.index', navigate: true);
    }

    public function getSubtotalProperty(): float
    {
        return round((float) collect($this->items)
            ->sum(fn (array $item): float => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 2);
    }

    public function getTaxAmountProperty(): float
    {
        $rate = (float) ($this->tax_rate !== '' ? $this->tax_rate : 0);

        return round($this->subtotal * ($rate / 100), 2);
    }

    public function getTotalAmountProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 2);
    }

    public function render()
    {
        $isEditing = $this->invoiceId !== null;

        return view('pages.invoices.form', [
            'clients' => $this->availableClients(),
            'products' => $this->availableProducts(),
            'plans' => $this->availablePlans(),
            'isEditing' => $isEditing,
        ])->layout('layouts.app', [
            'title' => $isEditing ? __('Edit Invoice') : __('Create Invoice'),
        ]);
    }

    private function fillFromInvoice(Invoice $invoice): void
    {
        $invoice->loadMissing(['items']);

        $this->client_id = (string) $invoice->client_id;
        $this->due_date = $invoice->due_date->toDateString();
        $this->tax_rate = (string) $invoice->tax_rate;
        $this->notes = $invoice->notes ?? '';
        $this->items = $invoice->items
            ->map(function ($item): array {
                $source = '';
                if ($item->catalog_product_id !== null) {
                    $source = 'product:'.$item->catalog_product_id;
                }
                if ($item->catalog_plan_id !== null) {
                    $source = 'plan:'.$item->catalog_plan_id;
                }

                return [
                    'source' => $source,
                    'catalog_product_id' => $item->catalog_product_id,
                    'catalog_plan_id' => $item->catalog_plan_id,
                    'description' => (string) $item->description,
                    'quantity' => (string) $item->quantity,
                    'unit_price' => (string) $item->unit_price,
                ];
            })
            ->values()
            ->all();

        if ($this->items === []) {
            $this->items = [$this->emptyItem()];
        }
    }

    private function syncItemFromSource(int $index, string $source, InvoiceService $invoiceService): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $trimmedSource = trim($source);
        if ($trimmedSource === '') {
            $this->items[$index]['catalog_product_id'] = null;
            $this->items[$index]['catalog_plan_id'] = null;

            return;
        }

        [$type, $id] = array_pad(explode(':', $trimmedSource, 2), 2, '');
        $resolvedId = is_numeric($id) ? (int) $id : 0;

        if ($resolvedId <= 0) {
            $this->items[$index]['source'] = '';
            $this->items[$index]['catalog_product_id'] = null;
            $this->items[$index]['catalog_plan_id'] = null;

            return;
        }

        if ($type === 'product') {
            $product = CatalogProduct::query()
                ->forUser((int) auth()->id())
                ->find($resolvedId);

            if ($product === null) {
                $this->items[$index]['source'] = '';
                $this->items[$index]['catalog_product_id'] = null;
                $this->items[$index]['catalog_plan_id'] = null;

                return;
            }

            $this->items[$index]['catalog_product_id'] = $product->id;
            $this->items[$index]['catalog_plan_id'] = null;
            $this->items[$index]['description'] = $product->name;
            $this->items[$index]['unit_price'] = number_format((float) $product->base_price, 2, '.', '');

            return;
        }

        if ($type === 'plan') {
            $plan = CatalogPlan::query()
                ->forUser((int) auth()->id())
                ->find($resolvedId);

            if ($plan === null) {
                $this->items[$index]['source'] = '';
                $this->items[$index]['catalog_product_id'] = null;
                $this->items[$index]['catalog_plan_id'] = null;

                return;
            }

            $this->items[$index]['catalog_product_id'] = null;
            $this->items[$index]['catalog_plan_id'] = $plan->id;
            $this->items[$index]['description'] = $plan->name;
            $this->items[$index]['unit_price'] = number_format(
                $invoiceService->planPrice($plan->id, (int) auth()->id()),
                2,
                '.',
                ''
            );

            return;
        }

        $this->items[$index]['source'] = '';
        $this->items[$index]['catalog_product_id'] = null;
        $this->items[$index]['catalog_plan_id'] = null;
    }

    private function normalizedItems(): array
    {
        return array_map(function (array $item): array {
            return [
                'source' => (string) ($item['source'] ?? ''),
                'catalog_product_id' => $item['catalog_product_id'] === '' ? null : ($item['catalog_product_id'] ?? null),
                'catalog_plan_id' => $item['catalog_plan_id'] === '' ? null : ($item['catalog_plan_id'] ?? null),
                'description' => (string) ($item['description'] ?? ''),
                'quantity' => (string) ($item['quantity'] ?? '1'),
                'unit_price' => (string) ($item['unit_price'] ?? '0'),
            ];
        }, $this->items);
    }

    private function normalizedValidatedItems(array $items): array
    {
        return array_map(function (array $item): array {
            return [
                'catalog_product_id' => $item['catalog_product_id'] ?? null,
                'catalog_plan_id' => $item['catalog_plan_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ];
        }, $items);
    }

    private function validateExclusiveSources(array $items): bool
    {
        $hasErrors = false;

        foreach ($items as $index => $item) {
            if (($item['catalog_product_id'] ?? null) !== null && ($item['catalog_plan_id'] ?? null) !== null) {
                $this->addError("items.$index.catalog_product_id", 'Select a product or a plan, not both.');
                $this->addError("items.$index.catalog_plan_id", 'Select a product or a plan, not both.');
                $hasErrors = true;
            }
        }

        return ! $hasErrors;
    }

    private function availableClients(): Collection
    {
        return Client::query()
            ->forUser((int) auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function availableProducts(): Collection
    {
        return CatalogProduct::query()
            ->forUser((int) auth()->id())
            ->activeOnly()
            ->applySort('name_asc')
            ->get(['id', 'name', 'base_price', 'currency']);
    }

    private function availablePlans(): Collection
    {
        return CatalogPlan::query()
            ->forUser((int) auth()->id())
            ->activeOnly()
            ->applySort('name_asc')
            ->get(['id', 'name', 'bundle_price', 'currency']);
    }

    private function emptyItem(): array
    {
        return [
            'source' => '',
            'catalog_product_id' => null,
            'catalog_plan_id' => null,
            'description' => '',
            'quantity' => '1',
            'unit_price' => '0',
        ];
    }
}
