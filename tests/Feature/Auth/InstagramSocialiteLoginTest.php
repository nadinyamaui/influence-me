<?php

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\User;
use App\Services\Auth\SocialiteLoginService;
use Laravel\Socialite\Facades\Socialite;

it('renders a instagram oauth login button', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Continue with Instagram');
    $response->assertSee(route('auth.instagram'));
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

    $response = $this->get(route('auth.instagram'));

    $response->assertRedirect('https://www.facebook.com/v18.0/dialog/oauth');
    $response->assertSessionHas('social_account_auth_intent', 'login');
});

it('requires authentication to connect additional instagram accounts', function (): void {
    $response = $this->get(route('auth.instagram.add'));

    $response->assertRedirect(route('login'));
});

it('redirects authenticated users to instagram provider for add-account flow', function (): void {
    $user = User::factory()->create();

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

    $response = $this->actingAs($user)->get(route('auth.instagram.add'));

    $response->assertRedirect('https://www.facebook.com/v18.0/dialog/oauth');
    $response->assertSessionHas('social_account_auth_intent', 'add_account');
});

it('redirects to dashboard after successful instagram callback', function (): void {
    $user = User::factory()->create();

    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createUserAndAccounts')
        ->once()
        ->andReturnUsing(function () use ($user) {
            auth()->login($user);

            return $user;
        });
    app()->instance(SocialiteLoginService::class, $loginService);

    $response = $this->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

it('returns to login when instagram oauth callback fails', function (): void {
    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createUserAndAccounts')
        ->once()
        ->andThrow(new RuntimeException('Denied'));
    $loginService->shouldReceive('driverLabel')
        ->once()
        ->andReturn('Instagram');
    app()->instance(SocialiteLoginService::class, $loginService);

    $response = $this->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'oauth' => 'Unable to complete Instagram sign in. Please try again.',
    ]);
    $this->assertGuest();
});

it('returns to login with social auth error message when callback raises social authentication exception', function (): void {
    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createUserAndAccounts')
        ->once()
        ->andThrow(new SocialAuthenticationException('Facebook denied access to the requested scopes.'));
    app()->instance(SocialiteLoginService::class, $loginService);

    $response = $this->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'oauth' => 'Facebook denied access to the requested scopes.',
    ]);
    $this->assertGuest();
});

it('uses account-linking flow on callback for authenticated users with add-account intent', function (): void {
    $user = User::factory()->create();

    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createSocialAccountsForLoggedUser')
        ->once()
        ->andReturn($user);
    $loginService->shouldNotReceive('createUserAndAccounts');
    app()->instance(SocialiteLoginService::class, $loginService);

    $response = $this->actingAs($user)
        ->withSession(['social_account_auth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('instagram-accounts.index'));
    $response->assertSessionHas('status', 'Instagram accounts connected successfully.');
});

it('returns to instagram accounts with oauth error on add-account callback social auth failure', function (): void {
    $user = User::factory()->create();

    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createSocialAccountsForLoggedUser')
        ->once()
        ->andThrow(new SocialAuthenticationException('Facebook denied account linking.'));
    $loginService->shouldNotReceive('createUserAndAccounts');
    app()->instance(SocialiteLoginService::class, $loginService);

    $response = $this->actingAs($user)
        ->withSession(['social_account_auth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('instagram-accounts.index'));
    $response->assertSessionHasErrors([
        'oauth' => 'Facebook denied account linking.',
    ]);
});

it('rate limits instagram oauth callback to ten attempts per minute per ip', function (): void {
    $user = User::factory()->create();

    $loginService = \Mockery::mock(SocialiteLoginService::class);
    $loginService->shouldReceive('createUserAndAccounts')
        ->times(10)
        ->andReturnUsing(function () use ($user) {
            auth()->login($user);

            return $user;
        });
    app()->instance(SocialiteLoginService::class, $loginService);

    foreach (range(1, 10) as $attempt) {
        $this->get(route('auth.instagram.callback'))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    $this->get(route('auth.instagram.callback'))
        ->assertStatus(429);
});
