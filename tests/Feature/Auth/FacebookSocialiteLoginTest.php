<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

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
    Socialite::shouldReceive('with')
        ->once()
        ->andReturnSelf();
    Socialite::shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://www.facebook.com/v18.0/dialog/oauth'));

    $response = $this->get(route('auth.facebook'));

    $response->assertRedirect('https://www.facebook.com/v18.0/dialog/oauth');
});

it('creates only a user after facebook callback for new users', function (): void {
    $socialiteUser = new SocialiteUser;
    $socialiteUser->id = '17841400000000000';
    $socialiteUser->nickname = 'creator.new';
    $socialiteUser->name = 'Creator New';
    $socialiteUser->email = 'creator.new@example.com';
    $socialiteUser->avatar = 'https://example.com/avatar.jpg';
    $socialiteUser->token = 'short-lived-token';
    $socialiteUser->user = [
        'username' => 'creator.new',
        'account_type' => 'business',
    ];

    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'creator.new@example.com')->first();
    expect($user)->not->toBeNull();

    expect($user?->instagramAccounts()->count())->toBe(0);
});

it('returns to login when facebook oauth callback fails', function (): void {
    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andThrow(new RuntimeException('Denied'));

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('oauth');
    $this->assertGuest();
});
