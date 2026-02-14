<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('renders the app sidebar with all RFC 013 navigation groups and links', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful()
        ->assertSeeText('Platform')
        ->assertSeeText('Manage')
        ->assertSeeText('Instagram')
        ->assertSeeTextInOrder(['Dashboard', 'Content', 'Schedule', 'Analytics', 'Clients', 'Proposals', 'Invoices', 'Accounts'])
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertSee('href="'.route('clients.index').'"', false)
        ->assertSee('href="'.route('instagram-accounts.index').'"', false)
        ->assertDontSee('Repository')
        ->assertDontSee('Documentation');

    expect(substr_count($response->getContent(), 'href="#"'))->toBeGreaterThanOrEqual(5);
});

it('renders the header layout variant with matching RFC 013 navigation links', function () {
    $path = '/_test/rfc-013/header-layout';

    Route::get($path, fn (): string => Blade::render(<<<'BLADE'
<x-layouts::app.header>
    <div>Header Layout Test Content</div>
</x-layouts::app.header>
BLADE
    ));

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get($path);

    $response->assertSuccessful()
        ->assertSeeText('Platform')
        ->assertSeeText('Manage')
        ->assertSeeText('Instagram')
        ->assertSeeTextInOrder(['Dashboard', 'Content', 'Schedule', 'Analytics', 'Clients', 'Proposals', 'Invoices', 'Accounts'])
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertSee('href="'.route('instagram-accounts.index').'"', false)
        ->assertDontSee('Repository')
        ->assertDontSee('Documentation');

    expect(substr_count($response->getContent(), 'href="#"'))->toBeGreaterThanOrEqual(12);
});
