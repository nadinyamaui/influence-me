<?php

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Index;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from invoices page', function (): void {
    $this->get(route('invoices.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users only see their own invoices and summary cards', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->for($user)->create(['name' => 'Acme Corp']);

    Invoice::factory()->for($user)->for($client)->sent()->create([
        'total' => 1200,
        'invoice_number' => '1001',
    ]);

    Invoice::factory()->for($user)->for($client)->overdue()->create([
        'total' => 300,
        'invoice_number' => '1002',
    ]);

    Invoice::factory()->for($user)->for($client)->paid()->create([
        'total' => 450,
        'paid_at' => now()->startOfMonth()->addDay(),
        'invoice_number' => '1003',
    ]);

    Invoice::factory()->for($user)->for($client)->paid()->create([
        'total' => 700,
        'paid_at' => now()->subMonth()->startOfMonth()->addDay(),
        'invoice_number' => '1004',
    ]);

    $otherClient = Client::factory()->for($otherUser)->create();
    Invoice::factory()->for($otherUser)->for($otherClient)->create([
        'invoice_number' => '9999',
    ]);

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('Invoices')
        ->assertSee('Acme Corp')
        ->assertSee('INV-')
        ->assertDontSee('9999')
        ->assertSee('$1,500.00')
        ->assertSee('$450.00');
});

test('invoices list filters by status', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $draftDueDate = now()->addDays(10);
    $sentDueDate = now()->addDays(20);
    $paidDueDate = now()->addDays(30);

    Invoice::factory()->for($user)->for($client)->draft()->create([
        'total' => 101,
        'due_date' => $draftDueDate->toDateString(),
    ]);
    Invoice::factory()->for($user)->for($client)->sent()->create([
        'total' => 202,
        'due_date' => $sentDueDate->toDateString(),
    ]);
    Invoice::factory()->for($user)->for($client)->paid()->create([
        'total' => 303,
        'due_date' => $paidDueDate->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('status', InvoiceStatus::Draft->value)
        ->assertSee($draftDueDate->format('M d, Y'))
        ->assertDontSee($sentDueDate->format('M d, Y'))
        ->assertDontSee($paidDueDate->format('M d, Y'))
        ->set('status', InvoiceStatus::Sent->value)
        ->assertSee($sentDueDate->format('M d, Y'))
        ->assertDontSee($draftDueDate->format('M d, Y'))
        ->assertDontSee($paidDueDate->format('M d, Y'))
        ->set('status', InvoiceStatus::Paid->value)
        ->assertSee($paidDueDate->format('M d, Y'))
        ->assertDontSee($draftDueDate->format('M d, Y'))
        ->assertDontSee($sentDueDate->format('M d, Y'));
});

test('invoices list filters by client', function (): void {
    $user = User::factory()->create();

    $clientA = Client::factory()->for($user)->create(['name' => 'Client Alpha']);
    $clientB = Client::factory()->for($user)->create(['name' => 'Client Beta']);

    Invoice::factory()->for($user)->for($clientA)->create(['invoice_number' => '3001']);
    Invoice::factory()->for($user)->for($clientB)->create(['invoice_number' => '3002']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('client', (string) $clientA->id)
        ->assertSee('3001')
        ->assertDontSee('3002')
        ->set('client', (string) $clientB->id)
        ->assertSee('3002')
        ->assertDontSee('3001')
        ->set('client', 'all')
        ->assertSee('3001')
        ->assertSee('3002');
});

test('invoices list shows correct status badge colors', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Invoice::factory()->for($user)->for($client)->draft()->create();
    Invoice::factory()->for($user)->for($client)->sent()->create();
    Invoice::factory()->for($user)->for($client)->paid()->create();
    Invoice::factory()->for($user)->for($client)->overdue()->create();
    Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Cancelled,
    ]);

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('bg-zinc-100')
        ->assertSee('bg-sky-100')
        ->assertSee('bg-emerald-100')
        ->assertSee('bg-rose-100');
});

test('invoices list highlights overdue due dates', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $dueDate = now()->subDay();

    Invoice::factory()->for($user)->for($client)->overdue()->create([
        'due_date' => $dueDate->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee($dueDate->format('M d, Y'))
        ->assertSee('text-rose-600');
});

test('invoices list paginates results', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    foreach (range(1, 11) as $number) {
        Invoice::factory()->for($user)->for($client)->create([
            'invoice_number' => (string) (5000 + $number),
            'created_at' => now()->subMinutes($number),
        ]);
    }

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('5001')
        ->assertDontSee('5011');

    $this->actingAs($user)
        ->get(route('invoices.index', ['page' => 2]))
        ->assertSuccessful()
        ->assertSee('5011')
        ->assertDontSee('5001');
});

test('invoices list shows empty state when user has no invoices', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('No invoices yet. Create your first invoice.');
});

test('sidebar link points to invoices index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('href="'.route('invoices.index').'"', false);
});

test('owners can delete draft invoices from the list page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->draft()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $invoice->id);

    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});

test('invoices list includes mobile card layout and desktop table layout', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Invoice::factory()->for($user)->for($client)->create([
        'invoice_number' => 'RESP-1001',
    ]);

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('invoice-card-', false)
        ->assertSee('class="hidden overflow-x-auto sm:block"', false);
});
