<?php

use App\Enums\ClientType;
use App\Livewire\Clients\Create;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from the client create page', function (): void {
    $this->get(route('clients.create'))
        ->assertRedirect(route('login'));
});

test('authenticated users can render the client create page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertSuccessful()
        ->assertSee('Add Client')
        ->assertSee('Client Name')
        ->assertSee('Company Name')
        ->assertSee('href="'.route('clients.index').'"', false);
});

test('authenticated users can create a client from the create form', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.name', 'Acme Partners')
        ->set('form.email', 'client@example.test')
        ->set('form.company_name', 'Acme Group')
        ->set('form.type', ClientType::Brand->value)
        ->set('form.phone', '555-111-2222')
        ->set('form.notes', 'Primary partner account.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('clients.index'));

    $client = Client::query()->first();

    expect($client)->not->toBeNull()
        ->and($client?->user_id)->toBe($user->id)
        ->and($client?->name)->toBe('Acme Partners')
        ->and($client?->type)->toBe(ClientType::Brand);
});

test('client create form validates invalid input', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.name', '')
        ->set('form.email', 'not-an-email')
        ->set('form.company_name', str_repeat('a', 256))
        ->set('form.type', 'invalid-type')
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

    $this->assertDatabaseCount('clients', 0);
});
