<?php

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\TaxRate;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from invoice create and edit pages', function (): void {
    $invoice = Invoice::factory()->create();

    $this->get(route('invoices.create'))->assertRedirect(route('login'));
    $this->get(route('invoices.edit', $invoice))->assertRedirect(route('login'));
});

test('invoice create and edit pages render through shared form component', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['name' => 'Northwind']);
    $invoice = Invoice::factory()->for($user)->for($client)->draft()->create();

    $this->actingAs($user)
        ->get(route('invoices.create'))
        ->assertSuccessful()
        ->assertSee('Create Invoice');

    $this->actingAs($user)
        ->get(route('invoices.edit', $invoice))
        ->assertSuccessful()
        ->assertSee('Edit Invoice');
});

test('influencer can create invoice with product plan and custom line items', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $product = CatalogProduct::factory()->for($user)->create([
        'name' => 'Reel Deliverable',
        'base_price' => 500,
        'currency' => 'USD',
    ]);
    $plan = CatalogPlan::factory()->for($user)->create([
        'name' => 'Launch Bundle',
        'bundle_price' => 1500,
        'currency' => 'USD',
    ]);

    Livewire::actingAs($user)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('due_date', now()->addDays(10)->toDateString())
        ->set('tax_rate', '10')
        ->set('notes', 'Net 14 days')
        ->set('items', [
            [
                'source' => 'product:'.$product->id,
                'catalog_product_id' => $product->id,
                'catalog_plan_id' => null,
                'description' => 'Reel Deliverable',
                'quantity' => '2',
                'unit_price' => '500',
            ],
            [
                'source' => 'plan:'.$plan->id,
                'catalog_product_id' => null,
                'catalog_plan_id' => $plan->id,
                'description' => 'Launch Bundle',
                'quantity' => '1',
                'unit_price' => '1500',
            ],
            [
                'source' => '',
                'catalog_product_id' => null,
                'catalog_plan_id' => null,
                'description' => 'Custom Creative Review',
                'quantity' => '1',
                'unit_price' => '250',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('invoices.index'));

    $invoice = Invoice::query()
        ->where('user_id', $user->id)
        ->latest('id')
        ->firstOrFail();

    expect($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and((float) $invoice->subtotal)->toBe(2750.0)
        ->and((float) $invoice->tax_amount)->toBe(275.0)
        ->and((float) $invoice->total)->toBe(3025.0);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'catalog_product_id' => $product->id,
        'catalog_plan_id' => null,
        'description' => 'Reel Deliverable',
        'quantity' => '2.00',
        'unit_price' => '500.00',
        'total' => '1000.00',
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'catalog_product_id' => null,
        'catalog_plan_id' => $plan->id,
        'description' => 'Launch Bundle',
        'quantity' => '1.00',
        'unit_price' => '1500.00',
        'total' => '1500.00',
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'catalog_product_id' => null,
        'catalog_plan_id' => null,
        'description' => 'Custom Creative Review',
        'quantity' => '1.00',
        'unit_price' => '250.00',
        'total' => '250.00',
    ]);
});

test('influencer can select a saved tax rate on invoice form', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $taxRate = TaxRate::factory()->for($user)->create([
        'label' => 'Sales Tax',
        'rate' => 7.5,
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('due_date', now()->addDays(10)->toDateString())
        ->set('tax_id', (string) $taxRate->id)
        ->set('items', [
            [
                'source' => '',
                'catalog_product_id' => null,
                'catalog_plan_id' => null,
                'description' => 'Consulting',
                'quantity' => '2',
                'unit_price' => '100',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('invoices.index'));

    $invoice = Invoice::query()
        ->where('user_id', $user->id)
        ->latest('id')
        ->firstOrFail();

    expect($invoice->tax_id)->toBe($taxRate->id)
        ->and((float) $invoice->tax_rate)->toBe(7.5)
        ->and((float) $invoice->subtotal)->toBe(200.0)
        ->and((float) $invoice->tax_amount)->toBe(15.0)
        ->and((float) $invoice->total)->toBe(215.0);
});

test('influencer can edit draft invoice using shared form component', function (): void {
    $user = User::factory()->create();
    $clientOne = Client::factory()->for($user)->create();
    $clientTwo = Client::factory()->for($user)->create();
    $plan = CatalogPlan::factory()->for($user)->create([
        'bundle_price' => null,
        'currency' => 'USD',
    ]);
    $planProduct = CatalogProduct::factory()->for($user)->create(['base_price' => 1200]);
    CatalogPlanItem::factory()->for($plan, 'catalogPlan')->create([
        'catalog_product_id' => $planProduct->id,
        'quantity' => 1,
    ]);

    $invoice = Invoice::factory()->for($user)->for($clientOne)->draft()->create([
        'tax_rate' => 0,
        'subtotal' => 1000,
        'tax_amount' => 0,
        'total' => 1000,
    ]);
    $invoice->items()->create([
        'description' => 'Old line',
        'quantity' => 1,
        'unit_price' => 1000,
        'total' => 1000,
    ]);

    Livewire::actingAs($user)
        ->test(InvoiceForm::class, ['invoice' => $invoice])
        ->set('client_id', (string) $clientTwo->id)
        ->set('due_date', now()->addDays(20)->toDateString())
        ->set('tax_rate', '5')
        ->set('notes', 'Updated notes')
        ->set('items', [
            [
                'source' => 'plan:'.$plan->id,
                'catalog_product_id' => null,
                'catalog_plan_id' => $plan->id,
                'description' => 'Plan row',
                'quantity' => '1',
                'unit_price' => '1200',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('invoices.index'));

    $invoice->refresh();

    expect($invoice->client_id)->toBe($clientTwo->id)
        ->and((float) $invoice->subtotal)->toBe(1200.0)
        ->and((float) $invoice->tax_amount)->toBe(60.0)
        ->and((float) $invoice->total)->toBe(1260.0)
        ->and($invoice->items()->count())->toBe(1);

    $this->assertDatabaseMissing('invoice_items', [
        'invoice_id' => $invoice->id,
        'description' => 'Old line',
    ]);
});

test('invoice form validates mutual exclusivity and ownership for source relations', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($user)->create();
    $outsiderProduct = CatalogProduct::factory()->for($outsider)->create();
    $outsiderPlan = CatalogPlan::factory()->for($outsider)->create();

    Livewire::actingAs($user)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('due_date', now()->addDays(7)->toDateString())
        ->set('items', [
            [
                'source' => '',
                'catalog_product_id' => $outsiderProduct->id,
                'catalog_plan_id' => $outsiderPlan->id,
                'description' => 'Invalid linked row',
                'quantity' => '1',
                'unit_price' => '100',
            ],
        ])
        ->call('save')
        ->assertHasErrors([
            'items.0.catalog_product_id',
            'items.0.catalog_plan_id',
        ]);
});

test('users cannot edit invoices they do not own or non-draft invoices', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $ownerClient = Client::factory()->for($owner)->create();
    $invoice = Invoice::factory()->for($owner)->for($ownerClient)->draft()->create();
    $sentInvoice = Invoice::factory()->for($owner)->for($ownerClient)->sent()->create();

    $this->actingAs($outsider)
        ->get(route('invoices.edit', $invoice))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('invoices.edit', $sentInvoice))
        ->assertForbidden();
});
