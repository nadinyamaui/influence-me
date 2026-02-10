<?php

namespace App\Services\Auth;

use App\Clients\Facebook\FacebookApiClient;
use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class InstagramOAuthService
{
    public function __construct(
        private readonly FacebookApiClient $facebookApiClient,
    ) {}

    /**
     * @return list<string>
     */
    public function oauthScopes(): array
    {
        return [
            'instagram_basic',
            'instagram_manage_insights',
            'pages_show_list',
            'pages_read_engagement',
            'business_management',
        ];
    }

    public function normalizeIntent(string $intent): string
    {
        return in_array($intent, ['login', 'add_account'], true) ? $intent : 'login';
    }

    public function failureRouteForIntent(string $intent): string
    {
        return $intent === 'add_account' ? 'dashboard' : 'login';
    }

    public function processCallback(string $intent, ?User $currentUser): InstagramOAuthResult
    {
        $socialiteUser = Socialite::driver('facebook')->user();

        $tokenData = $this->facebookApiClient->exchangeForLongLivedToken((string) $socialiteUser->token);
        $longLivedToken = (string) data_get($tokenData, 'access_token');
        $expiresIn = (int) data_get($tokenData, 'expires_in', 0);

        if ($longLivedToken === '' || $expiresIn <= 0) {
            throw new \RuntimeException('Meta token exchange returned incomplete payload.');
        }

        $instagramProfile = $this->facebookApiClient->resolveInstagramProfileFromAccessToken($longLivedToken);
        $actingUser = $intent === 'add_account' ? $currentUser : null;

        /** @var array{0:User,1:bool} $result */
        $result = DB::transaction(function () use ($actingUser, $expiresIn, $instagramProfile, $longLivedToken, $socialiteUser): array {
            $instagramUserId = (string) data_get($instagramProfile, 'id', '');

            if ($instagramUserId === '') {
                throw new \RuntimeException('Instagram account resolution returned no user id.');
            }

            $username = $this->resolveUsername($instagramProfile);
            $name = (string) data_get($instagramProfile, 'name', $username);

            $account = InstagramAccount::query()
                ->where('instagram_user_id', $instagramUserId)
                ->first();

            if ($actingUser instanceof User) {
                return $this->attachInstagramAccountForAuthenticatedUser(
                    actingUser: $actingUser,
                    existingAccount: $account,
                    instagramUserId: $instagramUserId,
                    username: $username,
                    name: $name,
                    instagramProfile: $instagramProfile,
                    accessToken: $longLivedToken,
                    expiresIn: $expiresIn,
                );
            }

            if ($account instanceof InstagramAccount) {
                $this->updateInstagramAccount(
                    account: $account,
                    username: $username,
                    name: $name,
                    instagramProfile: $instagramProfile,
                    accessToken: $longLivedToken,
                    expiresIn: $expiresIn,
                );

                return [$account->user, true];
            }

            $user = User::query()->create([
                'name' => (string) ($socialiteUser->getName() ?: $name),
                // Keep email nullable intent from RFC while supporting current schema constraints.
                'email' => $socialiteUser->getEmail() ?: sprintf('%s@instagram.local', $instagramUserId),
                'password' => null,
            ]);

            $newAccount = InstagramAccount::query()->create([
                'user_id' => $user->id,
                'instagram_user_id' => $instagramUserId,
                'username' => $username,
                'name' => $name,
                'profile_picture_url' => data_get($instagramProfile, 'profile_picture_url'),
                'account_type' => $this->resolveAccountType($instagramProfile),
                'access_token' => $longLivedToken,
                'token_expires_at' => now()->addSeconds($expiresIn),
                'is_primary' => true,
            ]);

            $user->forceFill([
                'instagram_primary_account_id' => $newAccount->id,
            ])->save();

            return [$user, true];
        });

        return new InstagramOAuthResult(
            user: $result[0],
            shouldLogin: $result[1],
        );
    }

    /**
     * @param  array<string, mixed>  $instagramProfile
     * @return array{0:User,1:bool}
     */
    private function attachInstagramAccountForAuthenticatedUser(
        User $actingUser,
        ?InstagramAccount $existingAccount,
        string $instagramUserId,
        string $username,
        string $name,
        array $instagramProfile,
        string $accessToken,
        int $expiresIn,
    ): array {
        if ($existingAccount instanceof InstagramAccount && $existingAccount->user_id !== $actingUser->id) {
            throw new \RuntimeException('This Instagram account is already connected to another user.');
        }

        if ($existingAccount instanceof InstagramAccount) {
            $this->updateInstagramAccount(
                account: $existingAccount,
                username: $username,
                name: $name,
                instagramProfile: $instagramProfile,
                accessToken: $accessToken,
                expiresIn: $expiresIn,
            );

            return [$actingUser, false];
        }

        $hasExistingAccounts = $actingUser->instagramAccounts()->exists();
        $isPrimary = ! $hasExistingAccounts;

        $newAccount = InstagramAccount::query()->create([
            'user_id' => $actingUser->id,
            'instagram_user_id' => $instagramUserId,
            'username' => $username,
            'name' => $name,
            'profile_picture_url' => data_get($instagramProfile, 'profile_picture_url'),
            'account_type' => $this->resolveAccountType($instagramProfile),
            'access_token' => $accessToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary || $actingUser->instagram_primary_account_id === null) {
            $actingUser->forceFill([
                'instagram_primary_account_id' => $newAccount->id,
            ])->save();
        }

        return [$actingUser, false];
    }

    /**
     * @param  array<string, mixed>  $instagramProfile
     */
    private function updateInstagramAccount(
        InstagramAccount $account,
        string $username,
        string $name,
        array $instagramProfile,
        string $accessToken,
        int $expiresIn,
    ): void {
        $account->update([
            'username' => $username,
            'name' => $name,
            'profile_picture_url' => data_get($instagramProfile, 'profile_picture_url'),
            'access_token' => $accessToken,
            'account_type' => $this->resolveAccountType($instagramProfile),
            'token_expires_at' => now()->addSeconds($expiresIn),
        ]);
    }

    /**
     * @param  array<string, mixed>  $instagramProfile
     */
    private function resolveUsername(array $instagramProfile): string
    {
        $username = (string) data_get($instagramProfile, 'username', '');

        if ($username !== '') {
            return $username;
        }

        $name = (string) data_get($instagramProfile, 'name', '');

        return Str::of($name)->slug('_')->toString() ?: 'instagram_user';
    }

    /**
     * @param  array<string, mixed>  $instagramProfile
     */
    private function resolveAccountType(array $instagramProfile): AccountType
    {
        $value = strtolower((string) data_get($instagramProfile, 'account_type', 'creator'));

        return $value === AccountType::Business->value
            ? AccountType::Business
            : AccountType::Creator;
    }
}
