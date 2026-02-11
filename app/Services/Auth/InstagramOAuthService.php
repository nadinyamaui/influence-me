<?php

namespace App\Services\Auth;

use App\Clients\Facebook\Contracts\FacebookOAuthClientInterface;
use App\Enums\AccountType;
use App\Enums\InstagramOAuthIntent;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
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
        if ($intent === InstagramOAuthIntent::AddAccount && ! ($currentUser instanceof User)) {
            throw new \RuntimeException(__('You must be logged in to add another Instagram account.'));
        }

        /** @var SocialiteUser $socialiteUser */
        $socialiteUser = Socialite::driver('facebook')
            ->fields($this->oauthProfileFields())
            ->user();

        $accessToken = (string) ($socialiteUser->token ?? '');
        $expiresIn = (int) ($socialiteUser->expiresIn ?? 0);

        if ($accessToken === '' || $expiresIn <= 0) {
            throw new \RuntimeException(__('Meta OAuth returned incomplete token payload.'));
        }

        $actingUser = $intent === InstagramOAuthIntent::AddAccount ? $currentUser : null;
        $instagramProfiles = $this->resolveInstagramProfiles($socialiteUser);
        $longLivedToken = $this->facebookOAuthClient->exchangeForLongLivedAccessToken($accessToken);
        $accessToken = $longLivedToken->accessToken;
        $expiresIn = $longLivedToken->expiresIn;

        /** @var array{0:User,1:bool} $result */
        $result = DB::transaction(function () use ($accessToken, $actingUser, $expiresIn, $instagramProfiles, $socialiteUser): array {
            if ($actingUser instanceof User) {
                $this->syncInstagramProfilesForUser(
                    user: $actingUser,
                    instagramProfiles: $instagramProfiles,
                    accessToken: $accessToken,
                    expiresIn: $expiresIn,
                );

                return [$actingUser, false];
            }

            $user = $this->resolveLoginUser(
                socialiteUser: $socialiteUser,
                instagramProfiles: $instagramProfiles,
            );

            $this->syncInstagramProfilesForUser(
                user: $user,
                instagramProfiles: $instagramProfiles,
                accessToken: $accessToken,
                expiresIn: $expiresIn,
            );

            return [$user, true];
        });

        return new InstagramOAuthResult(
            user: $result[0],
            shouldLogin: $result[1],
        );
    }

    private function resolveOrCreateOAuthUser(
        SocialiteUser $socialiteUser,
        string $fallbackName,
        string $instagramUserId,
    ): User {
        $displayName = (string) ($socialiteUser->getName() ?: $fallbackName);
        $email = trim((string) ($socialiteUser->getEmail() ?? ''));

        if ($email !== '') {
            $normalizedEmail = Str::lower($email);
            $existingUser = User::query()->where('email', $normalizedEmail)->first();

            if ($existingUser instanceof User) {
                return $existingUser;
            }

            return User::query()->create([
                'name' => $displayName,
                'email' => $normalizedEmail,
                'password' => null,
            ]);
        }

        return User::query()->create([
            'name' => $displayName,
            'email' => sprintf('%s@instagram.local', $instagramUserId),
            'password' => null,
        ]);
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
            'accounts{id,name,instagram_business_account{id,username,name,profile_picture_url}}',
        ];
    }

    /**
     * @param  Collection<int, object>  $instagramProfiles
     */
    private function syncInstagramProfilesForUser(
        User $user,
        Collection $instagramProfiles,
        string $accessToken,
        int $expiresIn,
    ): void {
        $hasExistingAccounts = $user->instagramAccounts()->exists();
        $assignPrimaryToNextCreated = ! $hasExistingAccounts;

        $instagramProfiles->each(function (object $instagramProfile) use (&$assignPrimaryToNextCreated, $accessToken, $expiresIn, $user): void {
            $instagramUserId = (string) ($instagramProfile?->id ?? '');
            if ($instagramUserId === '') {
                throw new \RuntimeException(__('Instagram account resolution returned no user id.'));
            }

            $username = $this->resolveUsername($instagramProfile);
            $name = (string) ($instagramProfile?->name ?? $username);
            $existingAccount = InstagramAccount::query()
                ->where('instagram_user_id', $instagramUserId)
                ->first();

            if ($existingAccount instanceof InstagramAccount) {
                if ($existingAccount->user_id !== $user->id) {
                    throw new \RuntimeException(__('This Instagram account is already connected to another user.'));
                }

                $this->updateInstagramAccount(
                    account: $existingAccount,
                    username: $username,
                    name: $name,
                    instagramProfile: $instagramProfile,
                    accessToken: $accessToken,
                    expiresIn: $expiresIn,
                );

                return;
            }

            $isPrimary = $assignPrimaryToNextCreated;
            InstagramAccount::query()->create([
                'user_id' => $user->id,
                'instagram_user_id' => $instagramUserId,
                'username' => $username,
                'name' => $name,
                'profile_picture_url' => $instagramProfile?->profile_picture_url,
                'account_type' => $this->resolveAccountType($instagramProfile),
                'access_token' => $accessToken,
                'token_expires_at' => now()->addSeconds($expiresIn),
                'is_primary' => $isPrimary,
            ]);

            if ($assignPrimaryToNextCreated) {
                $assignPrimaryToNextCreated = false;
            }
        });

        if ($user->instagram_primary_account_id === null) {
            $primaryAccountId = InstagramAccount::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->value('id');

            if ($primaryAccountId !== null) {
                $user->forceFill([
                    'instagram_primary_account_id' => $primaryAccountId,
                ])->save();
            }
        }
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

    /**
     * @return Collection<int, object>
     */
    private function resolveInstagramProfiles(SocialiteUser $socialiteUser): Collection
    {
        $raw = $socialiteUser->getRaw();
        $accounts = $raw['accounts']['data'] ?? [];

        if (! is_array($accounts)) {
            $accounts = [];
        }

        $profiles = collect($accounts)
            ->map(function (mixed $page): ?array {
                if (! is_array($page)) {
                    return null;
                }

                $account = $page['instagram_business_account'] ?? null;

                return is_array($account) ? $account : null;
            })
            ->filter(fn (mixed $account): bool => is_array($account) && (string) ($account['id'] ?? '') !== '')
            ->unique(fn (array $account): string => (string) ($account['id'] ?? ''))
            ->values()
            ->map(fn (array $account): object => $this->formatInstagramProfile($account));

        if ($profiles->isEmpty()) {
            throw new \RuntimeException(__('No Instagram professional account is linked to this Meta/Facebook user.'));
        }

        return $profiles;
    }

    /**
     * @param  Collection<int, object>  $instagramProfiles
     */
    private function resolveLoginUser(SocialiteUser $socialiteUser, Collection $instagramProfiles): User
    {
        $candidateIds = $instagramProfiles
            ->map(fn (object $profile): string => (string) ($profile?->id ?? ''))
            ->filter(fn (string $id): bool => $id !== '')
            ->values()
            ->all();

        $linkedUserIds = InstagramAccount::query()
            ->whereIn('instagram_user_id', $candidateIds)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($linkedUserIds->count() > 1) {
            throw new \RuntimeException(__('Meta returned Instagram accounts linked to multiple users.'));
        }

        if ($linkedUserIds->count() === 1) {
            $linkedUserId = (int) $linkedUserIds->first();
            $linkedUser = User::query()->find($linkedUserId);

            if ($linkedUser instanceof User) {
                return $linkedUser;
            }
        }

        $firstProfile = $instagramProfiles->first();
        $fallbackName = (string) ($firstProfile?->name ?? 'Instagram User');
        $fallbackInstagramUserId = (string) ($firstProfile?->id ?? '');

        return $this->resolveOrCreateOAuthUser(
            socialiteUser: $socialiteUser,
            fallbackName: $fallbackName,
            instagramUserId: $fallbackInstagramUserId,
        );
    }

    private function formatInstagramProfile(mixed $instagramBusinessAccount): object
    {
        if (! is_array($instagramBusinessAccount)) {
            throw new \RuntimeException(__('Instagram account resolution returned an invalid payload.'));
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
