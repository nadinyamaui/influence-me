# 050 - Invoice Create with Dynamic Line Items

**Labels:** `feature`, `invoicing`, `ui`, `pricing`
**Depends on:** #009, #012, #100, #101, #102

## Description

Create a Livewire page at `/invoices/create` with a form for creating invoices with dynamic line items and automatic total calculation.

Invoice line items must support mixed commercial sources:
- Pricing product (nullable `catalog_product_id`)
- Pricing plan (nullable `catalog_plan_id`)
- Custom line (both nullable relations are null)

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
- `items.*.catalog_product_id`: nullable, integer, exists:catalog_products,id
- `items.*.catalog_plan_id`: nullable, integer, exists:catalog_plans,id
- `items.*.description`: required, string, max:255
- `items.*.quantity`: required, numeric, min:0.01
- `items.*.unit_price`: required, numeric, min:0

Validation rules by source relation:
- `catalog_product_id` and `catalog_plan_id` are mutually exclusive.
- If `catalog_product_id` is set, it must reference an owned `catalog_products.id`.
- If `catalog_plan_id` is set, it must reference an owned `catalog_plans.id`.
- Custom row requires both `catalog_product_id` and `catalog_plan_id` to be null.

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
- Source Selector: choose owned product, owned plan, or leave unset for custom
- Description: `<flux:input>` (text)
- Quantity: `<flux:input>` (number)
- Unit Price: `<flux:input>` (number, prefix="$")
- Line Total: calculated display (quantity * unit_price)
- Remove button (X icon)

"Add Item" button below the list.

Source behavior:
- Product row pre-fills description and unit price from selected product snapshot and remains editable before save.
- Plan row pre-fills description and price from selected plan snapshot (bundle price if set, otherwise computed from plan composition) and remains editable before save.
- Custom row is fully manual.

**Totals Section (auto-calculated, read-only):**
- Subtotal: sum of all line totals
- Tax ({rate}%): subtotal * tax_rate / 100
- **Total: subtotal + tax**

### Livewire Logic
```php
public array $items = [
    ['catalog_product_id' => null, 'catalog_plan_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0],
];

public function addItem(): void
{
    $this->items[] = ['catalog_product_id' => null, 'catalog_plan_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0];
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
            'catalog_product_id' => $item['catalog_product_id'],
            'catalog_plan_id' => $item['catalog_plan_id'],
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
- `app/Models/InvoiceItem.php` — add nullable product/plan relations
- invoice item migration(s) — add nullable `catalog_product_id` and `catalog_plan_id` foreign keys

## Acceptance Criteria
- [ ] Form renders with dynamic line items
- [ ] Add/remove line item rows works
- [ ] Mixed-source rows are supported (product-linked, plan-linked, custom) in one invoice
- [ ] Product-linked rows require owned `catalog_product_id`
- [ ] Plan-linked rows require owned `catalog_plan_id`
- [ ] Custom rows save with both source relations null
- [ ] Product and plan relations are mutually exclusive per row
- [ ] Totals auto-calculate as values change
- [ ] Tax calculation correct
- [ ] Invoice number auto-generated
- [ ] All validation works
- [ ] Invoice + items saved correctly
- [ ] Feature tests cover creation with multiple items
