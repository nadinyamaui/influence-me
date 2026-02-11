<?php

use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
use Laravel\Socialite\Facades\Socialite;

it('renders a facebook oauth login button', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Continue with Facebook');
    $response->assertSee(route('auth.facebook'));
});

it('redirects to the facebook socialite provider', function (): void {
    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('scopes')
        ->once()
        ->with([
            'instagram_basic',
            'instagram_manage_insights',
            'pages_show_list',
            'pages_read_engagement',
        ])
        ->andReturnSelf();
    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://www.facebook.com/v18.0/dialog/oauth'));

    $response = $this->get(route('auth.facebook'));

    $response->assertRedirect('https://www.facebook.com/v18.0/dialog/oauth');
});

it('redirects to dashboard after successful facebook callback', function (): void {
    $user = User::factory()->create();

    $loginService = \Mockery::mock(FacebookSocialiteLoginService::class);
    $loginService->shouldReceive('resolveUserFromCallback')
        ->once()
        ->andReturnUsing(function () use ($user) {
            auth()->login($user);

            return $user;
        });
    app()->instance(FacebookSocialiteLoginService::class, $loginService);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

it('returns to login when facebook oauth callback fails', function (): void {
    $loginService = \Mockery::mock(FacebookSocialiteLoginService::class);
    $loginService->shouldReceive('resolveUserFromCallback')
        ->once()
        ->andThrow(new RuntimeException('Denied'));
    app()->instance(FacebookSocialiteLoginService::class, $loginService);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('oauth');
    $this->assertGuest();
});
