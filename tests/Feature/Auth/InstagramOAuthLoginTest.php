<?php

use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeMetaSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;

    $payload = array_merge([
        'id' => '102938475610',
        'nickname' => null,
        'name' => 'Meta User',
        'email' => null,
        'token' => 'short-lived-facebook-token',
        'raw' => [
            'id' => '102938475610',
            'name' => 'Meta User',
        ],
    ], $overrides);

    $user->id = (string) $payload['id'];
    $user->nickname = $payload['nickname'];
    $user->name = (string) $payload['name'];
    $user->email = $payload['email'];
    $user->token = (string) $payload['token'];
    $user->user = $payload['raw'];

    return $user;
}

it('shows login with instagram button on the login page', function (): void {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSeeText('Login with Instagram')
        ->assertDontSeeText('Email address');
});

it('redirects users to meta oauth with required scopes', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('scopes')->once()->with([
        'instagram_basic',
        'instagram_manage_insights',
        'pages_show_list',
        'pages_read_engagement',
        'business_management',
    ])->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://facebook.example/oauth'));

    $response = $this->get(route('auth.instagram'));

    $response->assertRedirect('https://facebook.example/oauth');
    expect((string) session('instagram_oauth_intent'))->toBe('login');
});

it('creates a user and instagram account for first-time oauth logins', function (): void {
    Http::fake([
        'graph.facebook.com/v23.0/oauth/access_token*' => Http::response([
            'access_token' => 'long-lived-meta-token',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
        ]),
        'graph.facebook.com/v23.0/me/accounts*' => Http::response([
            'data' => [[
                'id' => '9988776655',
                'name' => 'Creator Page',
                'instagram_business_account' => [
                    'id' => '17841499999999999',
                    'username' => 'new_creator',
                    'name' => 'New Creator',
                    'profile_picture_url' => 'https://cdn.example.com/new.jpg',
                ],
            ]],
        ]),
        'graph.facebook.com/v23.0/17841499999999999*' => Http::response([
            'id' => '17841499999999999',
            'username' => 'new_creator',
            'name' => 'New Creator',
            'account_type' => 'BUSINESS',
            'profile_picture_url' => 'https://cdn.example.com/new.jpg',
        ]),
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '102938475610',
        'name' => 'Meta User Name',
        'raw' => [
            'id' => '102938475610',
            'name' => 'Meta User Name',
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $account = InstagramAccount::query()->where('instagram_user_id', '17841499999999999')->firstOrFail();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($account->user_id)
        ->and($account->is_primary)->toBeTrue()
        ->and($account->account_type)->toBe(AccountType::Business)
        ->and($account->access_token)->toBe('long-lived-meta-token');

    $rawToken = DB::table('instagram_accounts')
        ->where('instagram_user_id', '17841499999999999')
        ->value('access_token');

    expect($rawToken)->not->toBe('long-lived-meta-token');
});

it('logs in returning users and refreshes their token', function (): void {
    $user = User::factory()->create();

    $account = InstagramAccount::factory()
        ->for($user)
        ->create([
            'instagram_user_id' => '17841411111111111',
            'username' => 'existing_creator',
            'access_token' => 'old-token',
            'is_primary' => true,
        ]);

    Http::fake([
        'graph.facebook.com/v23.0/oauth/access_token*' => Http::response([
            'access_token' => 'refreshed-long-token',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
        ]),
        'graph.facebook.com/v23.0/me/accounts*' => Http::response([
            'data' => [[
                'id' => '11223344',
                'name' => 'Existing Creator Page',
                'instagram_business_account' => [
                    'id' => '17841411111111111',
                    'username' => 'existing_creator',
                    'name' => 'Existing Creator',
                ],
            ]],
        ]),
        'graph.facebook.com/v23.0/17841411111111111*' => Http::response([
            'id' => '17841411111111111',
            'username' => 'existing_creator',
            'name' => 'Existing Creator',
            'account_type' => 'CREATOR',
        ]),
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '881122334455',
        'name' => 'Meta Existing User',
        'token' => 'existing-short-token',
        'raw' => [
            'id' => '881122334455',
            'name' => 'Meta Existing User',
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $account->refresh();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id)
        ->and($account->access_token)->toBe('refreshed-long-token')
        ->and($account->token_expires_at)->not->toBeNull();

    expect(User::query()->count())->toBe(1)
        ->and(InstagramAccount::query()->count())->toBe(1);
});

it('returns to login with an error when permissions are denied', function (): void {
    $response = $this->get(route('auth.instagram.callback', ['error' => 'access_denied']));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});

it('returns to login with an error when oauth state validation fails', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});

it('returns to login with an error when no linked instagram professional account exists', function (): void {
    Http::fake([
        'graph.facebook.com/v23.0/oauth/access_token*' => Http::response([
            'access_token' => 'long-lived-meta-token',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
        ]),
        'graph.facebook.com/v23.0/me/accounts*' => Http::response([
            'data' => [
                ['id' => '9988776655', 'name' => 'Page Without Instagram'],
            ],
        ]),
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser());

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});

it('returns to login with an error when oauth callback fails', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('OAuth failed'));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});

it('preserves add-account intent outside oauth state and redirects failures to dashboard', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('scopes')->once()->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://facebook.example/oauth'));

    $this->actingAs(User::factory()->create());

    $response = $this->get(route('auth.instagram', ['intent' => 'add_account']));

    $response->assertRedirect('https://facebook.example/oauth');
    expect((string) session('instagram_oauth_intent'))->toBe('add_account');

    $callbackProvider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($callbackProvider);
    $callbackProvider->shouldReceive('user')->once()->andThrow(new InvalidStateException);

    $callback = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $callback
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHasErrors('instagram');
});
