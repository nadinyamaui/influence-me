# 050 - Invoice Create with Dynamic Line Items

**Labels:** `feature`, `invoicing`, `ui`
**Depends on:** #009, #012

## Description

Create a Livewire page at `/invoices/create` with a form for creating invoices with dynamic line items and automatic total calculation.

## Implementation

### Create Route
```php
Route::livewire('invoices/create', 'invoices.create')
    ->middleware(['auth'])
    ->name('invoices.create');
```

### Create Form Request
`App\Http\Requests\StoreInvoiceRequest`:
- `client_id`: required, exists:clients,id
- `due_date`: required, date, after:today
- `tax_rate`: nullable, numeric, min:0, max:100
- `notes`: nullable, string, max:5000
- `items`: required, array, min:1
- `items.*.description`: required, string, max:255
- `items.*.quantity`: required, numeric, min:0.01
- `items.*.unit_price`: required, numeric, min:0

### Create Livewire Page
`resources/views/pages/invoices/create.blade.php`

### Form Fields

**Invoice Details:**
- Client: `<flux:select>` (required)
- Due Date: date picker (required)
- Tax Rate: `<flux:input>` type=number, suffix="%" (optional, default 0)
- Notes: `<flux:textarea>` (optional)

**Line Items Section:**
Dynamic list of items. Each row:
- Description: `<flux:input>` (text)
- Quantity: `<flux:input>` (number)
- Unit Price: `<flux:input>` (number, prefix="$")
- Line Total: calculated display (quantity * unit_price)
- Remove button (X icon)

"Add Item" button below the list.

**Totals Section (auto-calculated, read-only):**
- Subtotal: sum of all line totals
- Tax ({rate}%): subtotal * tax_rate / 100
- **Total: subtotal + tax**

### Livewire Logic
```php
public array $items = [
    ['description' => '', 'quantity' => 1, 'unit_price' => 0],
];

public function addItem(): void
{
    $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
}

public function removeItem(int $index): void
{
    unset($this->items[$index]);
    $this->items = array_values($this->items);
}

public function getSubtotalProperty(): float
{
    return collect($this->items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
}

public function save(): void
{
    $validated = $this->validate(/* rules */);

    $invoice = auth()->user()->invoices()->create([
        'client_id' => $this->clientId,
        'invoice_number' => Invoice::generateInvoiceNumber(),
        'status' => InvoiceStatus::Draft,
        'due_date' => $this->dueDate,
        'tax_rate' => $this->taxRate ?? 0,
        'notes' => $this->notes,
    ]);

    foreach ($this->items as $item) {
        $invoice->items()->create([
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total' => $item['quantity'] * $item['unit_price'],
        ]);
    }

    $invoice->calculateTotals();
    $invoice->save();

    redirect()->route('invoices.show', $invoice);
}
```

## Files to Create
- `resources/views/pages/invoices/create.blade.php`
- `app/Http/Requests/StoreInvoiceRequest.php`

## Files to Modify
- `routes/web.php` — add route

## Acceptance Criteria
- [ ] Form renders with dynamic line items
- [ ] Add/remove line item rows works
- [ ] Totals auto-calculate as values change
- [ ] Tax calculation correct
- [ ] Invoice number auto-generated
- [ ] All validation works
- [ ] Invoice + items saved correctly
- [ ] Feature tests cover creation with multiple items
