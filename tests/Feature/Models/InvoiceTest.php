<?php

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('creates valid invoice records with factory defaults and casts', function (): void {
    $invoice = Invoice::factory()->create();

    expect($invoice->user)->toBeInstanceOf(User::class)
        ->and($invoice->client)->toBeInstanceOf(Client::class)
        ->and($invoice->invoice_number)->toBe((string) $invoice->id)
        ->and($invoice->status)->toBeInstanceOf(InvoiceStatus::class)
        ->and($invoice->due_date)->not->toBeNull();
});

it('supports draft sent paid and overdue factory states', function (): void {
    $draft = Invoice::factory()->draft()->create();
    $sent = Invoice::factory()->sent()->create();
    $paid = Invoice::factory()->paid()->create();
    $overdue = Invoice::factory()->overdue()->create();

    expect($draft->status)->toBe(InvoiceStatus::Draft)
        ->and($sent->status)->toBe(InvoiceStatus::Sent)
        ->and($paid->status)->toBe(InvoiceStatus::Paid)
        ->and($paid->paid_at)->not->toBeNull()
        ->and($overdue->status)->toBe(InvoiceStatus::Overdue)
        ->and($overdue->due_date->isPast())->toBeTrue();
});

it('defines user client and items relationships with typed returns', function (): void {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->create();

    $itemsReturnType = (new ReflectionMethod(Invoice::class, 'items'))
        ->getReturnType()?->getName();

    expect($invoice->user())->toBeInstanceOf(BelongsTo::class)
        ->and($invoice->client())->toBeInstanceOf(BelongsTo::class)
        ->and($itemsReturnType)->toBe(HasMany::class)
        ->and($invoice->items)->toHaveCount(1)
        ->and($invoice->items->first())->toBeInstanceOf(InvoiceItem::class);
});

it('recalculates totals from invoice items', function (): void {
    $invoice = Invoice::factory()->create([
        'tax_rate' => 10,
        'subtotal' => 0,
        'tax_amount' => 0,
        'total' => 0,
    ]);

    InvoiceItem::factory()->for($invoice)->create([
        'quantity' => 1,
        'unit_price' => 100,
        'total' => 100,
    ]);

    InvoiceItem::factory()->for($invoice)->create([
        'quantity' => 2,
        'unit_price' => 50,
        'total' => 100,
    ]);

    $invoice->calculateTotals();
    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(200.0)
        ->and((float) $invoice->tax_amount)->toBe(20.0)
        ->and((float) $invoice->total)->toBe(220.0);
});

it('uses invoice id as invoice number', function (): void {
    $invoice = Invoice::factory()->create();

    expect($invoice->invoice_number)->toBe((string) $invoice->id);
});

it('defines invoice item invoice relationship and casts', function (): void {
    $item = InvoiceItem::factory()->create();

    expect($item->invoice())->toBeInstanceOf(BelongsTo::class)
        ->and($item->invoice)->toBeInstanceOf(Invoice::class)
        ->and($item->quantity)->toBeString()
        ->and($item->unit_price)->toBeString()
        ->and($item->total)->toBeString();
});

it('defines user invoices relationship', function (): void {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create();
    Invoice::factory()->for($user)->create();

    expect($user->invoices())->toBeInstanceOf(HasMany::class)
        ->and($user->invoices)->toHaveCount(2);
});
