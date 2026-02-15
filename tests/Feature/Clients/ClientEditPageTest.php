<?php

use App\Enums\ClientType;
use App\Livewire\Clients\Edit;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from the client edit page', function (): void {
    $client = Client::factory()->create();

    $this->get(route('clients.edit', $client))
        ->assertRedirect(route('login'));
});

test('client edit page renders with prefilled data for the owner', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create([
        'name' => 'Northline Brand',
        'email' => 'northline@example.test',
        'company_name' => 'Northline Co',
        'phone' => '555-200-3000',
        'notes' => 'Existing notes',
    ]);

    $this->actingAs($user)
        ->get(route('clients.edit', $client))
        ->assertSuccessful()
        ->assertSee('Edit Client')
        ->assertSee('Northline Brand')
        ->assertSee('northline@example.test')
        ->assertSee('Northline Co')
        ->assertSee('Existing notes');
});

test('non-owners cannot access client edit page', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('clients.edit', $client))
        ->assertForbidden();
});

test('owners can update client details', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create([
        'name' => 'Before Name',
        'type' => ClientType::Individual,
    ]);

    Livewire::actingAs($owner)
        ->test(Edit::class, ['client' => $client])
        ->set('form.name', 'After Name')
        ->set('form.email', 'after@example.test')
        ->set('form.company_name', 'After Co')
        ->set('form.type', ClientType::Brand->value)
        ->set('form.phone', '555-999-0000')
        ->set('form.notes', 'Updated notes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('clients.show', $client));

    $updatedClient = $client->fresh();

    expect($updatedClient?->name)->toBe('After Name')
        ->and($updatedClient?->email)->toBe('after@example.test')
        ->and($updatedClient?->company_name)->toBe('After Co')
        ->and($updatedClient?->type)->toBe(ClientType::Brand)
        ->and($updatedClient?->phone)->toBe('555-999-0000')
        ->and($updatedClient?->notes)->toBe('Updated notes');
});

test('client edit form validates invalid updates', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    Livewire::actingAs($owner)
        ->test(Edit::class, ['client' => $client])
        ->set('form.name', '')
        ->set('form.email', 'invalid-email')
        ->set('form.company_name', str_repeat('c', 256))
        ->set('form.type', 'wrong')
        ->set('form.phone', str_repeat('1', 51))
        ->set('form.notes', str_repeat('n', 5001))
        ->call('save')
        ->assertHasErrors([
            'form.name',
            'form.email' => 'email',
            'form.company_name',
            'form.type',
            'form.phone',
            'form.notes',
        ]);
});

test('owners can delete clients from edit page and related proposals and invoices are cascaded', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $proposal = Proposal::factory()->for($owner)->for($client)->create();
    $invoice = Invoice::factory()->for($owner)->for($client)->create();

    Livewire::actingAs($owner)
        ->test(Edit::class, ['client' => $client])
        ->call('confirmDelete')
        ->assertSet('confirmingDelete', true)
        ->call('delete')
        ->assertRedirect(route('clients.index'));

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});
