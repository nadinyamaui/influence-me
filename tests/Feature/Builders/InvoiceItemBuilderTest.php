<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;

it('scopes invoice items by invoice id', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $invoiceA = Invoice::factory()->for($user)->for($client)->create();
    $invoiceB = Invoice::factory()->for($user)->for($client)->create();

    $itemA = InvoiceItem::factory()->for($invoiceA)->create();
    InvoiceItem::factory()->for($invoiceB)->create();

    $ids = InvoiceItem::query()
        ->forInvoice($invoiceA->id)
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$itemA->id]);
});

it('creates invoice items with invoice scoped helper', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    $item = InvoiceItem::query()->createForInvoice([
        'catalog_product_id' => null,
        'catalog_plan_id' => null,
        'description' => 'Custom line',
        'quantity' => 1,
        'unit_price' => 400,
        'total' => 400,
    ], $invoice->id);

    expect($item->invoice_id)->toBe($invoice->id)
        ->and($item->description)->toBe('Custom line');
});
