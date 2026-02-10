<?php

namespace App\Services\Auth;

use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class InstagramOAuthService
{
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

        $tokenData = $this->exchangeForLongLivedToken((string) $socialiteUser->token);
        $longLivedToken = (string) data_get($tokenData, 'access_token');
        $expiresIn = (int) data_get($tokenData, 'expires_in', 0);

        if ($longLivedToken === '' || $expiresIn <= 0) {
            throw new \RuntimeException('Meta token exchange returned incomplete payload.');
        }

        $instagramProfile = $this->resolveInstagramProfileFromFacebookToken($longLivedToken);
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
     * @return array<string, mixed>
     */
    private function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get('https://graph.facebook.com/v23.0/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array{id:string,username:string,name:?string,account_type:?string,profile_picture_url:?string}
     */
    private function resolveInstagramProfileFromFacebookToken(string $accessToken): array
    {
        $pagesResponse = Http::get('https://graph.facebook.com/v23.0/me/accounts', [
            'access_token' => $accessToken,
            'fields' => 'id,name,instagram_business_account{id,username,name,profile_picture_url}',
            'limit' => 100,
        ]);

        $pagesResponse->throw();

        $pages = data_get($pagesResponse->json(), 'data', []);

        if (! is_array($pages)) {
            $pages = [];
        }

        $instagramBusinessAccount = collect($pages)
            ->map(fn (mixed $page): mixed => is_array($page) ? data_get($page, 'instagram_business_account') : null)
            ->first(fn (mixed $account): bool => is_array($account) && (string) data_get($account, 'id', '') !== '');

        if (! is_array($instagramBusinessAccount)) {
            throw new \RuntimeException('No Instagram professional account is linked to this Meta/Facebook user.');
        }

        $instagramUserId = (string) data_get($instagramBusinessAccount, 'id');

        $profileResponse = Http::get(sprintf('https://graph.facebook.com/v23.0/%s', $instagramUserId), [
            'access_token' => $accessToken,
            'fields' => 'id,username,name,account_type,profile_picture_url',
        ]);

        if ($profileResponse->failed()) {
            Log::warning('Failed to fetch Instagram profile detail after OAuth callback.', [
                'instagram_user_id' => $instagramUserId,
                'status' => $profileResponse->status(),
            ]);

            return [
                'id' => $instagramUserId,
                'username' => (string) data_get($instagramBusinessAccount, 'username', 'instagram_user'),
                'name' => data_get($instagramBusinessAccount, 'name'),
                'account_type' => null,
                'profile_picture_url' => data_get($instagramBusinessAccount, 'profile_picture_url'),
            ];
        }

        $profile = $profileResponse->json();

        return [
            'id' => (string) data_get($profile, 'id', $instagramUserId),
            'username' => (string) data_get($profile, 'username', data_get($instagramBusinessAccount, 'username', 'instagram_user')),
            'name' => data_get($profile, 'name', data_get($instagramBusinessAccount, 'name')),
            'account_type' => data_get($profile, 'account_type'),
            'profile_picture_url' => data_get($profile, 'profile_picture_url', data_get($instagramBusinessAccount, 'profile_picture_url')),
        ];
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

