<?php

use App\Enums\ClientType;
use App\Livewire\Clients\Index;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from clients page', function (): void {
    $this->get(route('clients.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users only see their own clients and sidebar link points to clients index', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownerClient = Client::factory()->for($user)->create([
        'name' => 'Acme Labs',
        'email' => 'owner@example.test',
        'type' => ClientType::Brand,
        'company_name' => 'Acme Corporation',
    ]);

    Client::factory()->for($otherUser)->create([
        'name' => 'Hidden Client',
    ]);

    $account = InstagramAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();
    $ownerClient->instagramMedia()->attach($media->id);

    $response = $this->actingAs($user)->get(route('clients.index'));

    $response->assertSuccessful()
        ->assertSee('Clients')
        ->assertSee('Acme Labs')
        ->assertSee('Acme Corporation')
        ->assertSee('owner@example.test')
        ->assertSee('Brand')
        ->assertSee('1')
        ->assertDontSee('Hidden Client')
        ->assertSee('href="'.route('clients.index').'"', false);
});

test('clients list supports search by name email and company', function (): void {
    $user = User::factory()->create();

    Client::factory()->for($user)->create([
        'name' => 'North Star Co',
        'email' => 'north@example.test',
        'company_name' => 'North Labs',
    ]);

    Client::factory()->for($user)->create([
        'name' => 'South Wind Co',
        'email' => 'south@example.test',
        'company_name' => 'South Labs',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'North Star')
        ->assertSee('North Star Co')
        ->assertDontSee('South Wind Co')
        ->set('search', 'south@example.test')
        ->assertSee('South Wind Co')
        ->assertDontSee('North Star Co')
        ->set('search', 'North Labs')
        ->assertSee('North Star Co')
        ->assertDontSee('South Wind Co');
});

test('clients list supports filtering by client type', function (): void {
    $user = User::factory()->create();

    Client::factory()->for($user)->create([
        'name' => 'Brand Profile',
        'type' => ClientType::Brand,
    ]);

    Client::factory()->for($user)->create([
        'name' => 'Individual Profile',
        'type' => ClientType::Individual,
        'company_name' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('type', ClientType::Brand->value)
        ->assertSee('Brand Profile')
        ->assertDontSee('Individual Profile')
        ->set('type', ClientType::Individual->value)
        ->assertSee('Individual Profile')
        ->assertDontSee('Brand Profile');
});

test('clients list paginates results', function (): void {
    $user = User::factory()->create();

    foreach (range(1, 11) as $number) {
        Client::factory()->for($user)->create([
            'name' => 'Client '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertSuccessful()
        ->assertSee('Client 01')
        ->assertDontSee('Client 11');

    $this->actingAs($user)
        ->get(route('clients.index', ['page' => 2]))
        ->assertSuccessful()
        ->assertSee('Client 11')
        ->assertDontSee('Client 01');
});

test('clients list shows empty state when user has no clients', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertSuccessful()
        ->assertSee('No clients yet. Add your first client to start managing campaigns.');
});

test('owners can delete clients from the list page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('confirmDelete', $client->id)
        ->assertSet('deletingClientId', $client->id)
        ->call('delete')
        ->assertSet('deletingClientId', null);

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
});
