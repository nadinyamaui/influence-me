<?php

use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeInstagramSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;

    $payload = array_merge([
        'id' => '17841400000000000',
        'nickname' => 'creator_handle',
        'name' => 'Creator Name',
        'email' => null,
        'token' => 'short-lived-token',
        'raw' => [
            'id' => '17841400000000000',
            'username' => 'creator_handle',
            'account_type' => 'creator',
        ],
    ], $overrides);

    $user->id = (string) $payload['id'];
    $user->nickname = (string) $payload['nickname'];
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

it('redirects users to instagram oauth with required scopes', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('instagram')->andReturn($provider);
    $provider->shouldReceive('scopes')->once()->with([
        'instagram_basic',
        'instagram_manage_insights',
        'pages_show_list',
        'pages_read_engagement',
    ])->andReturnSelf();
    $provider->shouldReceive('with')->once()->with(Mockery::on(function (array $payload): bool {
        return isset($payload['state'])
            && str_starts_with((string) $payload['state'], 'login|');
    }))->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://instagram.example/oauth'));

    $response = $this->get(route('auth.instagram'));

    $response->assertRedirect('https://instagram.example/oauth');
    expect((string) session('instagram_oauth_state'))->toStartWith('login|');
});

it('creates a user and instagram account for first-time oauth logins', function (): void {
    Http::fake([
        'graph.instagram.com/access_token*' => Http::response([
            'access_token' => 'long-lived-token',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
        ]),
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('instagram')->andReturn($provider);
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeInstagramSocialiteUser([
        'id' => '17841499999999999',
        'nickname' => 'new_creator',
        'name' => 'New Creator',
        'raw' => [
            'id' => '17841499999999999',
            'username' => 'new_creator',
            'account_type' => 'business',
        ],
    ]));

    $state = 'login|csrf-token-value';
    $response = $this
        ->withSession(['instagram_oauth_state' => $state])
        ->get(route('auth.instagram.callback', ['state' => $state]));

    $response->assertRedirect(route('dashboard', absolute: false));

    $account = InstagramAccount::query()->where('instagram_user_id', '17841499999999999')->firstOrFail();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($account->user_id)
        ->and($account->is_primary)->toBeTrue()
        ->and($account->account_type)->toBe(AccountType::Business)
        ->and($account->access_token)->toBe('long-lived-token');

    $rawToken = DB::table('instagram_accounts')
        ->where('instagram_user_id', '17841499999999999')
        ->value('access_token');

    expect($rawToken)->not->toBe('long-lived-token');
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
        'graph.instagram.com/access_token*' => Http::response([
            'access_token' => 'refreshed-long-token',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
        ]),
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('instagram')->andReturn($provider);
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeInstagramSocialiteUser([
        'id' => '17841411111111111',
        'nickname' => 'existing_creator',
        'name' => 'Existing Creator',
        'token' => 'existing-short-token',
        'raw' => [
            'id' => '17841411111111111',
            'username' => 'existing_creator',
            'account_type' => 'creator',
        ],
    ]));

    $state = 'login|csrf-token-value';
    $response = $this
        ->withSession(['instagram_oauth_state' => $state])
        ->get(route('auth.instagram.callback', ['state' => $state]));

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

it('returns to login with an error when oauth state is missing or invalid', function (): void {
    $response = $this
        ->withSession(['instagram_oauth_state' => 'login|expected-state'])
        ->get(route('auth.instagram.callback', ['state' => 'login|other-state']));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});

it('returns to login with an error when oauth callback fails', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('instagram')->andReturn($provider);
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('OAuth failed'));

    $state = 'login|csrf-token-value';
    $response = $this
        ->withSession(['instagram_oauth_state' => $state])
        ->get(route('auth.instagram.callback', ['state' => $state]));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
});
