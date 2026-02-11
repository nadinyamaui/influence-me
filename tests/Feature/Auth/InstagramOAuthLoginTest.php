<?php

use App\Clients\Facebook\Contracts\FacebookOAuthClientInterface;
use App\Clients\Facebook\Data\FacebookLongLivedAccessToken;
use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        'expires_in' => 5183944,
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
    $user->expiresIn = (int) $payload['expires_in'];
    $user->user = $payload['raw'];

    return $user;
}

function mockFacebookTokenExchange(string $expectedToken, string $returnedToken): void
{
    $client = Mockery::mock(FacebookOAuthClientInterface::class);
    $client->shouldReceive('exchangeForLongLivedAccessToken')
        ->once()
        ->with($expectedToken)
        ->andReturn(new FacebookLongLivedAccessToken(
            accessToken: $returnedToken,
            expiresIn: 5183944,
        ));

    app()->instance(FacebookOAuthClientInterface::class, $client);
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

it('downgrades add-account intent to login when unauthenticated', function (): void {
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

    $response = $this->get(route('auth.instagram', ['intent' => 'add_account']));

    $response->assertRedirect('https://facebook.example/oauth');
    expect((string) session('instagram_oauth_intent'))->toBe('login');
});

it('requests oauth profile fields without unsupported instagram account_type', function (): void {
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')
        ->once()
        ->with(Mockery::on(function (array $fields): bool {
            $requestedAccountsField = 'accounts{id,name,instagram_business_account{id,username,name,profile_picture_url}}';
            $containsUnsupportedField = collect($fields)->contains(fn (string $field): bool => str_contains($field, 'account_type'));

            return in_array($requestedAccountsField, $fields, true) && ! $containsUnsupportedField;
        }))
        ->andReturnSelf();
    $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('OAuth failed'));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('instagram');
});

it('creates a user and instagram account for first-time oauth logins', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'long-lived-meta-token',
        returnedToken: 'exchanged-long-lived-token',
    );

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '102938475610',
        'name' => 'Meta User Name',
        'token' => 'long-lived-meta-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '102938475610',
            'name' => 'Meta User Name',
            'accounts' => [
                'data' => [[
                    'id' => '9988776655',
                    'name' => 'Creator Page',
                    'instagram_business_account' => [
                        'id' => '17841499999999999',
                        'username' => 'new_creator',
                        'name' => 'New Creator',
                        'profile_picture_url' => 'https://cdn.example.com/new.jpg',
                        'account_type' => 'BUSINESS',
                    ],
                ]],
            ],
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
        ->and($account->access_token)->toBe('exchanged-long-lived-token');

    $rawToken = DB::table('instagram_accounts')
        ->where('instagram_user_id', '17841499999999999')
        ->value('access_token');

    expect($rawToken)->not->toBe('exchanged-long-lived-token');
});

it('reuses an existing user when oauth email already exists', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'existing-email-token',
        returnedToken: 'exchanged-existing-email-token',
    );

    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'instagram_primary_account_id' => null,
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '555666777888',
        'name' => 'Existing Influencer',
        'email' => 'existing@example.com',
        'token' => 'existing-email-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '555666777888',
            'name' => 'Existing Influencer',
            'email' => 'existing@example.com',
            'accounts' => [
                'data' => [[
                    'id' => '12345678',
                    'name' => 'Existing Email Page',
                    'instagram_business_account' => [
                        'id' => '17841444444444444',
                        'username' => 'existing_email_creator',
                        'name' => 'Existing Email Creator',
                        'account_type' => 'CREATOR',
                    ],
                ]],
            ],
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $newAccount = InstagramAccount::query()->where('instagram_user_id', '17841444444444444')->firstOrFail();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($existingUser->id)
        ->and($newAccount->user_id)->toBe($existingUser->id)
        ->and($newAccount->is_primary)->toBeTrue()
        ->and($existingUser->fresh()->instagram_primary_account_id)->toBe($newAccount->id)
        ->and(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
});

it('preserves an existing primary instagram account when reusing user by email', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'existing-primary-token',
        returnedToken: 'exchanged-existing-primary-token',
    );

    $existingUser = User::factory()->create([
        'email' => 'primary@example.com',
    ]);

    $existingPrimary = InstagramAccount::factory()
        ->for($existingUser)
        ->primary()
        ->create([
            'instagram_user_id' => '17841455555555555',
            'username' => 'existing_primary',
        ]);

    $existingUser->forceFill([
        'instagram_primary_account_id' => $existingPrimary->id,
    ])->save();

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '888999000111',
        'name' => 'Primary Preserved User',
        'email' => 'primary@example.com',
        'token' => 'existing-primary-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '888999000111',
            'name' => 'Primary Preserved User',
            'email' => 'primary@example.com',
            'accounts' => [
                'data' => [[
                    'id' => '22334455',
                    'name' => 'Secondary Creator Page',
                    'instagram_business_account' => [
                        'id' => '17841466666666666',
                        'username' => 'secondary_creator',
                        'name' => 'Secondary Creator',
                        'account_type' => 'CREATOR',
                    ],
                ]],
            ],
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $newAccount = InstagramAccount::query()->where('instagram_user_id', '17841466666666666')->firstOrFail();

    expect($newAccount->user_id)->toBe($existingUser->id)
        ->and($newAccount->is_primary)->toBeFalse()
        ->and($existingPrimary->fresh()->is_primary)->toBeTrue()
        ->and($existingUser->fresh()->instagram_primary_account_id)->toBe($existingPrimary->id)
        ->and(InstagramAccount::query()->where('user_id', $existingUser->id)->where('is_primary', true)->count())->toBe(1);
});

it('logs in returning users and refreshes their token', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'refreshed-long-token',
        returnedToken: 'exchanged-refreshed-long-token',
    );

    $user = User::factory()->create();

    $account = InstagramAccount::factory()
        ->for($user)
        ->create([
            'instagram_user_id' => '17841411111111111',
            'username' => 'existing_creator',
            'access_token' => 'old-token',
            'is_primary' => true,
        ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '881122334455',
        'name' => 'Meta Existing User',
        'token' => 'refreshed-long-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '881122334455',
            'name' => 'Meta Existing User',
            'accounts' => [
                'data' => [[
                    'id' => '11223344',
                    'name' => 'Existing Creator Page',
                    'instagram_business_account' => [
                        'id' => '17841411111111111',
                        'username' => 'existing_creator',
                        'name' => 'Existing Creator',
                        'account_type' => 'CREATOR',
                    ],
                ]],
            ],
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $account->refresh();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id)
        ->and($account->access_token)->toBe('exchanged-refreshed-long-token')
        ->and($account->token_expires_at)->not->toBeNull();

    expect(User::query()->count())->toBe(1)
        ->and(InstagramAccount::query()->count())->toBe(1);
});

it('selects the single locally linked instagram account when meta returns multiple pages', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'multi-pages-token',
        returnedToken: 'exchanged-multi-pages-token',
    );

    $user = User::factory()->create();
    $linkedAccount = InstagramAccount::factory()
        ->for($user)
        ->create([
            'instagram_user_id' => '17841477777777777',
            'username' => 'linked_creator',
            'access_token' => 'old-linked-token',
            'is_primary' => true,
        ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '121212121212',
        'name' => 'Meta Multi Page User',
        'token' => 'multi-pages-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '121212121212',
            'name' => 'Meta Multi Page User',
            'accounts' => [
                'data' => [
                    [
                        'id' => '100001',
                        'name' => 'Unlinked Page',
                        'instagram_business_account' => [
                            'id' => '17841488888888888',
                            'username' => 'unlinked_creator',
                            'name' => 'Unlinked Creator',
                            'account_type' => 'CREATOR',
                        ],
                    ],
                    [
                        'id' => '100002',
                        'name' => 'Linked Page',
                        'instagram_business_account' => [
                            'id' => '17841477777777777',
                            'username' => 'linked_creator',
                            'name' => 'Linked Creator',
                            'account_type' => 'BUSINESS',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $linkedAccount->refresh();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id)
        ->and($linkedAccount->access_token)->toBe('exchanged-multi-pages-token')
        ->and($linkedAccount->account_type)->toBe(AccountType::Business)
        ->and(InstagramAccount::query()->where('user_id', $user->id)->count())->toBe(2);
});

it('creates instagram accounts for all meta pages in login flow', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'ambiguous-token',
        returnedToken: 'exchanged-ambiguous-token',
    );

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '343434343434',
        'name' => 'Meta Ambiguous User',
        'email' => 'ambiguous@example.com',
        'token' => 'ambiguous-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '343434343434',
            'name' => 'Meta Ambiguous User',
            'email' => 'ambiguous@example.com',
            'accounts' => [
                'data' => [
                    [
                        'id' => '200001',
                        'name' => 'First Page',
                        'instagram_business_account' => [
                            'id' => '17841410101010101',
                            'username' => 'first_ambiguous',
                            'name' => 'First Ambiguous',
                            'account_type' => 'CREATOR',
                        ],
                    ],
                    [
                        'id' => '200002',
                        'name' => 'Second Page',
                        'instagram_business_account' => [
                            'id' => '17841420202020202',
                            'username' => 'second_ambiguous',
                            'name' => 'Second Ambiguous',
                            'account_type' => 'BUSINESS',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'login'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $user = User::query()->where('email', 'ambiguous@example.com')->firstOrFail();
    $firstAccount = InstagramAccount::query()->where('instagram_user_id', '17841410101010101')->firstOrFail();
    $secondAccount = InstagramAccount::query()->where('instagram_user_id', '17841420202020202')->firstOrFail();

    expect($firstAccount->user_id)->toBe($user->id)
        ->and($secondAccount->user_id)->toBe($user->id)
        ->and(InstagramAccount::query()->where('user_id', $user->id)->count())->toBe(2)
        ->and(InstagramAccount::query()->where('user_id', $user->id)->where('is_primary', true)->count())->toBe(1);
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
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
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
    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
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
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
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
    $callbackProvider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $callbackProvider->shouldReceive('user')->once()->andThrow(new InvalidStateException);

    $callback = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $callback
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHasErrors('instagram');
});

it('rejects add-account callbacks when no influencer is authenticated', function (): void {
    $response = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHasErrors('instagram');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0)
        ->and(InstagramAccount::query()->count())->toBe(0);
});

it('honors add-account intent by attaching the resolved account to the authenticated user without session switching', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'new-add-account-token',
        returnedToken: 'exchanged-add-account-token',
    );

    $currentUser = User::factory()->create();
    $otherUser = User::factory()->create();
    $existingPrimary = InstagramAccount::factory()->for($currentUser)->primary()->create();

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '998877001122',
        'name' => 'Meta Add Account User',
        'token' => 'new-add-account-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '998877001122',
            'name' => 'Meta Add Account User',
            'accounts' => [
                'data' => [[
                    'id' => '77700011',
                    'name' => 'Second Creator Page',
                    'instagram_business_account' => [
                        'id' => '17841422222222222',
                        'username' => 'second_creator',
                        'name' => 'Second Creator',
                        'account_type' => 'CREATOR',
                    ],
                ]],
            ],
        ],
    ]));

    $this->actingAs($currentUser);

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $newAccount = InstagramAccount::query()->where('instagram_user_id', '17841422222222222')->firstOrFail();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($currentUser->id)
        ->and($newAccount->user_id)->toBe($currentUser->id)
        ->and($newAccount->is_primary)->toBeFalse()
        ->and($newAccount->access_token)->toBe('exchanged-add-account-token')
        ->and($existingPrimary->fresh()->is_primary)->toBeTrue()
        ->and(User::query()->count())->toBe(2)
        ->and(InstagramAccount::query()->where('user_id', $otherUser->id)->count())->toBe(0);
});

it('attaches all returned instagram accounts during add-account intent', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'add-multiple-token',
        returnedToken: 'exchanged-add-multiple-token',
    );

    $currentUser = User::factory()->create();
    $existingPrimary = InstagramAccount::factory()->for($currentUser)->primary()->create();

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '909090909090',
        'name' => 'Meta Add Multiple User',
        'token' => 'add-multiple-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '909090909090',
            'name' => 'Meta Add Multiple User',
            'accounts' => [
                'data' => [
                    [
                        'id' => '99880011',
                        'name' => 'Third Creator Page',
                        'instagram_business_account' => [
                            'id' => '17841430303030303',
                            'username' => 'third_creator',
                            'name' => 'Third Creator',
                            'account_type' => 'CREATOR',
                        ],
                    ],
                    [
                        'id' => '99880022',
                        'name' => 'Fourth Creator Page',
                        'instagram_business_account' => [
                            'id' => '17841440404040404',
                            'username' => 'fourth_creator',
                            'name' => 'Fourth Creator',
                            'account_type' => 'BUSINESS',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $this->actingAs($currentUser);

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));

    $thirdAccount = InstagramAccount::query()->where('instagram_user_id', '17841430303030303')->firstOrFail();
    $fourthAccount = InstagramAccount::query()->where('instagram_user_id', '17841440404040404')->firstOrFail();

    expect($thirdAccount->user_id)->toBe($currentUser->id)
        ->and($fourthAccount->user_id)->toBe($currentUser->id)
        ->and($thirdAccount->is_primary)->toBeFalse()
        ->and($fourthAccount->is_primary)->toBeFalse()
        ->and($existingPrimary->fresh()->is_primary)->toBeTrue()
        ->and(InstagramAccount::query()->where('user_id', $currentUser->id)->count())->toBe(3);
});

it('does not attach another users instagram account during add-account intent', function (): void {
    mockFacebookTokenExchange(
        expectedToken: 'conflict-token',
        returnedToken: 'exchanged-conflict-token',
    );

    $currentUser = User::factory()->create();
    $owner = User::factory()->create();

    InstagramAccount::factory()->for($owner)->create([
        'instagram_user_id' => '17841433333333333',
        'username' => 'owned_elsewhere',
    ]);

    $provider = Mockery::mock();

    Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);
    $provider->shouldReceive('fields')->once()->with(Mockery::type('array'))->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(fakeMetaSocialiteUser([
        'id' => '222244445555',
        'name' => 'Meta Conflict User',
        'token' => 'conflict-token',
        'expires_in' => 5183944,
        'raw' => [
            'id' => '222244445555',
            'name' => 'Meta Conflict User',
            'accounts' => [
                'data' => [[
                    'id' => '22223333',
                    'name' => 'Conflict Page',
                    'instagram_business_account' => [
                        'id' => '17841433333333333',
                        'username' => 'owned_elsewhere',
                        'name' => 'Owned Elsewhere',
                        'account_type' => 'BUSINESS',
                    ],
                ]],
            ],
        ],
    ]));

    $this->actingAs($currentUser);

    $response = $this
        ->withSession(['instagram_oauth_intent' => 'add_account'])
        ->get(route('auth.instagram.callback'));

    $response
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHasErrors('instagram');

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($currentUser->id)
        ->and(InstagramAccount::query()->where('user_id', $currentUser->id)->count())->toBe(0);
});
