<?php

namespace App\Services\Auth;

use App\Clients\Facebook\Contracts\FacebookOAuthClientInterface;
use App\Enums\AccountType;
use App\Enums\InstagramOAuthIntent;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class InstagramOAuthService
{
    public function __construct(
        private readonly FacebookOAuthClientInterface $facebookOAuthClient,
    ) {}

    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->scopes($this->oauthScopes())
            ->redirect();
    }

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

    public function processCallback(InstagramOAuthIntent $intent, ?User $currentUser): InstagramOAuthResult
    {
        /** @var SocialiteUser $socialiteUser */
        $socialiteUser = Socialite::driver('facebook')
            ->fields($this->oauthProfileFields())
            ->user();

        $accessToken = (string) ($socialiteUser->token ?? '');
        $expiresIn = (int) ($socialiteUser->expiresIn ?? 0);

        if ($accessToken === '' || $expiresIn <= 0) {
            throw new \RuntimeException(__('Meta OAuth returned incomplete token payload.'));
        }

        $instagramProfile = $this->resolveInstagramProfile($socialiteUser);
        $longLivedToken = $this->facebookOAuthClient->exchangeForLongLivedAccessToken($accessToken);
        $accessToken = $longLivedToken->accessToken;
        $expiresIn = $longLivedToken->expiresIn;
        $actingUser = $intent === InstagramOAuthIntent::AddAccount ? $currentUser : null;

        /** @var array{0:User,1:bool} $result */
        $result = DB::transaction(function () use ($accessToken, $actingUser, $expiresIn, $instagramProfile, $socialiteUser): array {
            $instagramUserId = (string) ($instagramProfile?->id ?? '');

            if ($instagramUserId === '') {
                throw new \RuntimeException(__('Instagram account resolution returned no user id.'));
            }

            $username = $this->resolveUsername($instagramProfile);
            $name = (string) ($instagramProfile?->name ?? $username);

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
                    accessToken: $accessToken,
                    expiresIn: $expiresIn,
                );
            }

            if ($account instanceof InstagramAccount) {
                $this->updateInstagramAccount(
                    account: $account,
                    username: $username,
                    name: $name,
                    instagramProfile: $instagramProfile,
                    accessToken: $accessToken,
                    expiresIn: $expiresIn,
                );

                return [$account->user, true];
            }

            $user = User::query()->create([
                'name' => (string) ($socialiteUser->getName() ?: $name),
                'email' => $socialiteUser->getEmail() ?: sprintf('%s@instagram.local', $instagramUserId),
                'password' => null,
            ]);

            $newAccount = InstagramAccount::query()->create([
                'user_id' => $user->id,
                'instagram_user_id' => $instagramUserId,
                'username' => $username,
                'name' => $name,
                'profile_picture_url' => $instagramProfile?->profile_picture_url,
                'account_type' => $this->resolveAccountType($instagramProfile),
                'access_token' => $accessToken,
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
     * @return list<string>
     */
    private function oauthProfileFields(): array
    {
        return [
            'id',
            'name',
            'email',
            'accounts{id,name,instagram_business_account{id,username,name,profile_picture_url,account_type}}',
        ];
    }

    /**
     * @return array{0:User,1:bool}
     */
    private function attachInstagramAccountForAuthenticatedUser(
        User $actingUser,
        ?InstagramAccount $existingAccount,
        string $instagramUserId,
        string $username,
        string $name,
        object $instagramProfile,
        string $accessToken,
        int $expiresIn,
    ): array {
        if ($existingAccount instanceof InstagramAccount && $existingAccount->user_id !== $actingUser->id) {
            throw new \RuntimeException(__('This Instagram account is already connected to another user.'));
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
            'profile_picture_url' => $instagramProfile?->profile_picture_url,
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

    private function updateInstagramAccount(
        InstagramAccount $account,
        string $username,
        string $name,
        object $instagramProfile,
        string $accessToken,
        int $expiresIn,
    ): void {
        $account->update([
            'username' => $username,
            'name' => $name,
            'profile_picture_url' => $instagramProfile?->profile_picture_url,
            'access_token' => $accessToken,
            'account_type' => $this->resolveAccountType($instagramProfile),
            'token_expires_at' => now()->addSeconds($expiresIn),
        ]);
    }

    private function resolveUsername(object $instagramProfile): string
    {
        $username = (string) ($instagramProfile?->username ?? '');

        if ($username !== '') {
            return $username;
        }

        $name = (string) ($instagramProfile?->name ?? '');

        return Str::of($name)->slug('_')->toString() ?: 'instagram_user';
    }

    private function resolveAccountType(object $instagramProfile): AccountType
    {
        $value = strtolower((string) ($instagramProfile?->account_type ?? 'creator'));

        return $value === AccountType::Business->value
            ? AccountType::Business
            : AccountType::Creator;
    }

    private function resolveInstagramProfile(SocialiteUser $socialiteUser): object
    {
        $raw = $socialiteUser->getRaw();
        $accounts = $raw['accounts']['data'] ?? [];

        if (! is_array($accounts)) {
            $accounts = [];
        }

        $instagramBusinessAccount = collect($accounts)
            ->map(function (mixed $page): ?array {
                if (! is_array($page)) {
                    return null;
                }

                $account = $page['instagram_business_account'] ?? null;

                return is_array($account) ? $account : null;
            })
            ->first(fn (mixed $account): bool => is_array($account) && (string) ($account['id'] ?? '') !== '');

        if (! is_array($instagramBusinessAccount)) {
            throw new \RuntimeException(__('No Instagram professional account is linked to this Meta/Facebook user.'));
        }

        return (object) [
            'id' => (string) ($instagramBusinessAccount['id'] ?? ''),
            'username' => (string) ($instagramBusinessAccount['username'] ?? 'instagram_user'),
            'name' => $instagramBusinessAccount['name'] ?? null,
            'account_type' => $instagramBusinessAccount['account_type'] ?? null,
            'profile_picture_url' => $instagramBusinessAccount['profile_picture_url'] ?? null,
        ];
    }
}
