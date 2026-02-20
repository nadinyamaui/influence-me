# 009 - Invoice and InvoiceItem Models and Factories

**Labels:** `feature`, `foundation`, `invoicing`
**Depends on:** #001, #002, #006

## Description

Create `Invoice` and `InvoiceItem` Eloquent models with relationships, casts, and factories. Update User model with relationship.

## Model: `App\Models\Invoice`

### Mass Assignment
Use:
```php
protected $guarded = [];
```

Expected persisted attributes:
`user_id`, `client_id`, `invoice_number`, `status`, `due_date`, `subtotal`, `tax_rate`, `tax_amount`, `total`, `paid_at`, `notes`

### Casts
```php
protected function casts(): array
{
    return [
        'status' => InvoiceStatus::class,
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
```

### Relationships
- `belongsTo(User::class)`
- `belongsTo(Client::class)`
- `hasMany(InvoiceItem::class)`

### Methods
- `calculateTotals(): void` - recalculates subtotal, tax_amount, total from line items
- `generateInvoiceNumber(): string` - generates next sequential number (INV-YYYY-NNNN)

### Factory States
- Default: draft invoice
- `draft()`: status = Draft
- `sent()`: status = Sent
- `paid()`: status = Paid, paid_at filled
- `overdue()`: status = Overdue, due_date in past

---

## Model: `App\Models\InvoiceItem`

### Mass Assignment
Use:
```php
protected $guarded = [];
```

Expected persisted attributes:
`invoice_id`, `description`, `quantity`, `unit_price`, `total`

### Casts
```php
protected function casts(): array
{
    return [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];
}
```

### Relationships
- `belongsTo(Invoice::class)`

### Factory
- Default: generates realistic line item (e.g., "Instagram Post - Product Review")

---

### User Model Update
Add to `app/Models/User.php`:
```php
public function invoices(): HasMany
{
    return $this->hasMany(Invoice::class);
}
```

## Files to Create/Modify
- `app/Models/Invoice.php`
- `app/Models/InvoiceItem.php`
- `database/factories/InvoiceFactory.php`
- `database/factories/InvoiceItemFactory.php`
- `app/Models/User.php` (add relationship)

## Acceptance Criteria
- [ ] Both models created with `protected $guarded = [];` and required casts
- [ ] Relationships defined with return type hints
- [ ] `calculateTotals()` correctly sums line items
- [ ] `generateInvoiceNumber()` produces sequential numbers
- [ ] Factories produce valid instances with all states
- [ ] User model has `invoices()` relationship
- [ ] Tests verify factories, relationships, and calculation methods
