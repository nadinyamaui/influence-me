<?php

use App\Models\InstagramAccount;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('users without instagram accounts see the dashboard onboarding state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Explore your dashboard with demo-ready tools')
        ->assertSee('Connecting Instagram is optional and can be done later when you are ready.');
});

test('users with linked instagram accounts do not see the dashboard onboarding state', function () {
    $user = User::factory()->create();
    InstagramAccount::factory()->for($user)->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertDontSee('Explore your dashboard with demo-ready tools')
        ->assertDontSee('Connecting Instagram is optional and can be done later when you are ready.');
});
