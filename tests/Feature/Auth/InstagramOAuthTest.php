<?php

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
use Laravel\Socialite\Facades\Socialite;

it('redirects to instagram oauth provider', function (): void {
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
    $response->assertSessionHas('facebook_auth_intent', 'login');
});

it('creates a new user and instagram account on callback', function (): void {
    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'New Influencer';
        }

        public function getEmail(): string
        {
            return 'new-influencer@example.com';
        }
    };

    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $service = \Mockery::mock(FacebookSocialiteLoginService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $service->shouldReceive('exchangeToken')
        ->once()
        ->with($socialiteUser)
        ->andReturn(['access_token' => 'long-lived-token']);
    $service->shouldReceive('getAccounts')
        ->once()
        ->with('1234567890123', 'long-lived-token')
        ->andReturn(collect([
            [
                'instagram_user_id' => 'ig-1',
                'username' => 'new_ig',
                'name' => 'New IG',
                'biography' => 'Bio',
                'profile_picture_url' => 'https://example.test/ig-1.jpg',
                'followers_count' => 100,
                'following_count' => 50,
                'media_count' => 25,
                'access_token' => 'page-token-1',
            ],
        ]));
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::query()->count())->toBe(1)
        ->and(InstagramAccount::query()->count())->toBe(1);

    $user = User::query()->firstOrFail();

    expect($user->socialite_user_type)->toBe('facebook')
        ->and($user->socialite_user_id)->toBe('1234567890123')
        ->and($user->email)->toBe('new-influencer@example.com');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('instagram_accounts', [
        'user_id' => $user->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'new_ig',
    ]);
});

it('logs in returning user and updates token on callback', function (): void {
    $user = User::factory()->create([
        'socialite_user_type' => 'facebook',
        'socialite_user_id' => '1234567890123',
    ]);

    InstagramAccount::factory()->create([
        'user_id' => $user->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'existing_ig',
        'access_token' => 'old-token',
    ]);

    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'Updated Influencer';
        }

        public function getEmail(): string
        {
            return 'updated@example.com';
        }
    };

    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $service = \Mockery::mock(FacebookSocialiteLoginService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $service->shouldReceive('exchangeToken')
        ->once()
        ->with($socialiteUser)
        ->andReturn(['access_token' => 'new-long-lived-token']);
    $service->shouldReceive('getAccounts')
        ->once()
        ->with('1234567890123', 'new-long-lived-token')
        ->andReturn(collect([
            [
                'instagram_user_id' => 'ig-1',
                'username' => 'updated_ig',
                'name' => 'Updated IG',
                'biography' => 'Updated bio',
                'profile_picture_url' => 'https://example.test/ig-1-updated.jpg',
                'followers_count' => 700,
                'following_count' => 70,
                'media_count' => 30,
                'access_token' => 'new-page-token',
            ],
        ]));
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user->fresh());

    $updated = InstagramAccount::query()
        ->where('instagram_user_id', 'ig-1')
        ->firstOrFail();

    expect($updated->username)->toBe('updated_ig')
        ->and($updated->access_token)->toBe('new-page-token');
});

it('handles denied permissions from oauth callback', function (): void {
    $service = \Mockery::mock(FacebookSocialiteLoginService::class);
    $service->shouldReceive('createUserAndAccounts')
        ->once()
        ->andThrow(new SocialAuthenticationException('Facebook denied access to the requested scopes.'));
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'oauth' => 'Facebook denied access to the requested scopes.',
    ]);
    $this->assertGuest();
});

it('uses exchanged long-lived token to fetch instagram accounts', function (): void {
    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'Token User';
        }

        public function getEmail(): string
        {
            return 'token-user@example.com';
        }
    };

    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    $service = \Mockery::mock(FacebookSocialiteLoginService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $service->shouldReceive('exchangeToken')
        ->once()
        ->with($socialiteUser)
        ->andReturn(['access_token' => 'long-lived-token']);
    $service->shouldReceive('getAccounts')
        ->once()
        ->with('1234567890123', 'long-lived-token')
        ->andReturn(collect());
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $response = $this->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

it('adds additional instagram account for authenticated user', function (): void {
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

    $entryResponse = $this->actingAs($user)->get(route('auth.facebook.add'));

    $entryResponse->assertRedirect('https://www.facebook.com/v18.0/dialog/oauth');
    $entryResponse->assertSessionHas('facebook_auth_intent', 'add_account');

    $service = \Mockery::mock(FacebookSocialiteLoginService::class);
    $service->shouldReceive('createInstagramAccountsForLoggedUser')
        ->once()
        ->andReturn($user);
    $service->shouldNotReceive('createUserAndAccounts');
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $response = $this->actingAs($user)->get(route('auth.facebook.callback'));

    $response->assertRedirect(route('instagram-accounts.index'));
    $response->assertSessionHas('status', 'Instagram accounts connected successfully.');
});

it('allows oauth users to logout', function (): void {
    $user = User::factory()->create([
        'socialite_user_type' => 'facebook',
        'socialite_user_id' => '1234567890123',
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});
