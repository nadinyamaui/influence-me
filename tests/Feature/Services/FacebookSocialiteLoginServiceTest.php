<?php

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

it('redirects to facebook provider with required scopes', function (): void {
    $expectedRedirect = redirect('https://www.facebook.com/v18.0/dialog/oauth');

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
        ->andReturn($expectedRedirect);

    $response = app(FacebookSocialiteLoginService::class)->redirectToProvider();

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('https://www.facebook.com/v18.0/dialog/oauth');
});

it('throws a social authentication exception when facebook does not return an id', function (): void {
    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): ?string
        {
            return null;
        }
    };

    Socialite::shouldReceive('driver')
        ->once()
        ->with('facebook')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    app(FacebookSocialiteLoginService::class)->resolveUserFromCallback();
})->throws(SocialAuthenticationException::class, 'Facebook did not return required account information.');

it('creates the influencer user, logs them in, and syncs instagram accounts on callback', function (): void {
    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'Social User';
        }

        public function getEmail(): string
        {
            return 'social@example.com';
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
                'username' => 'ig_one',
                'name' => 'IG One',
                'biography' => 'Bio one',
                'profile_picture_url' => 'https://example.test/ig-1.jpg',
                'followers_count' => 1000,
                'following_count' => 150,
                'media_count' => 42,
                'access_token' => 'page-token-1',
            ],
            [
                'instagram_user_id' => 'ig-2',
                'username' => 'ig_two',
                'name' => 'IG Two',
                'biography' => 'Bio two',
                'profile_picture_url' => 'https://example.test/ig-2.jpg',
                'followers_count' => 3000,
                'following_count' => 250,
                'media_count' => 96,
                'access_token' => 'page-token-2',
            ],
        ]));

    $resolvedUser = $service->resolveUserFromCallback();

    expect($resolvedUser->socialite_user_type)->toBe('facebook')
        ->and($resolvedUser->socialite_user_id)->toBe('1234567890123')
        ->and($resolvedUser->email)->toBe('social@example.com');
    $this->assertAuthenticatedAs($resolvedUser);
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('instagram_accounts', [
        'user_id' => $resolvedUser->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'ig_one',
        'followers_count' => 1000,
    ]);
    $this->assertDatabaseHas('instagram_accounts', [
        'user_id' => $resolvedUser->id,
        'instagram_user_id' => 'ig-2',
        'username' => 'ig_two',
        'followers_count' => 3000,
    ]);
});

it('updates existing user and instagram account records on callback', function (): void {
    $existingUser = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'socialite_user_type' => 'facebook',
        'socialite_user_id' => '1234567890123',
    ]);

    InstagramAccount::factory()->create([
        'user_id' => $existingUser->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'old_ig_username',
        'followers_count' => 25,
        'access_token' => 'old-page-token',
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
            return 'Updated Name';
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
        ->andReturn(['access_token' => 'long-lived-token']);
    $service->shouldReceive('getAccounts')
        ->once()
        ->with('1234567890123', 'long-lived-token')
        ->andReturn(collect([
            [
                'instagram_user_id' => 'ig-1',
                'username' => 'new_ig_username',
                'name' => 'Updated IG',
                'biography' => 'Updated bio',
                'profile_picture_url' => 'https://example.test/updated-ig-1.jpg',
                'followers_count' => 999,
                'following_count' => 111,
                'media_count' => 50,
                'access_token' => 'new-page-token',
            ],
        ]));

    $resolvedUser = $service->resolveUserFromCallback();

    expect($resolvedUser->id)->toBe($existingUser->id)
        ->and($resolvedUser->name)->toBe('Updated Name')
        ->and($resolvedUser->email)->toBe('updated@example.com');
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('instagram_accounts', 1);
    $this->assertDatabaseHas('instagram_accounts', [
        'user_id' => $existingUser->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'new_ig_username',
        'followers_count' => 999,
    ]);
});

it('links instagram accounts to the currently authenticated user without creating a new user', function (): void {
    $authenticatedUser = User::factory()->create([
        'socialite_user_type' => null,
        'socialite_user_id' => null,
    ]);
    $this->actingAs($authenticatedUser);

    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'Social User';
        }

        public function getEmail(): string
        {
            return 'social@example.com';
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
                'username' => 'ig_one',
                'name' => 'IG One',
                'biography' => 'Bio one',
                'profile_picture_url' => 'https://example.test/ig-1.jpg',
                'followers_count' => 1000,
                'following_count' => 150,
                'media_count' => 42,
                'access_token' => 'page-token-1',
            ],
        ]));

    $resolvedUser = $service->resolveUserFromCallback();

    expect($resolvedUser->id)->toBe($authenticatedUser->id);
    $this->assertAuthenticatedAs($authenticatedUser);
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('instagram_accounts', [
        'user_id' => $authenticatedUser->id,
        'instagram_user_id' => 'ig-1',
        'username' => 'ig_one',
        'followers_count' => 1000,
    ]);
});

it('throws when callback includes an instagram account already linked to another user', function (): void {
    $authenticatedUser = User::factory()->create();
    $otherUser = User::factory()->create();
    InstagramAccount::factory()->create([
        'user_id' => $otherUser->id,
        'instagram_user_id' => 'ig-1',
    ]);
    $this->actingAs($authenticatedUser);

    $socialiteUser = new class
    {
        public string $token = 'short-lived-token';

        public function getId(): string
        {
            return '1234567890123';
        }

        public function getName(): string
        {
            return 'Social User';
        }

        public function getEmail(): string
        {
            return 'social@example.com';
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
                'username' => 'ig_one',
                'name' => 'IG One',
                'biography' => 'Bio one',
                'profile_picture_url' => 'https://example.test/ig-1.jpg',
                'followers_count' => 1000,
                'following_count' => 150,
                'media_count' => 42,
                'access_token' => 'page-token-1',
            ],
        ]));

    $service->resolveUserFromCallback();
})->throws(SocialAuthenticationException::class, 'This Instagram account is already linked to another user.');
