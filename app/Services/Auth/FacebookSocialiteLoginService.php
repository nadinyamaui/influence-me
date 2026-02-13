<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\Facebook\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Laravel\Socialite\Facades\Socialite;

class FacebookSocialiteLoginService
{
    private array $scopes = [
        'instagram_basic',
        'instagram_manage_insights',
        'pages_show_list',
        'pages_read_engagement',
    ];

    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->scopes($this->scopes)
            ->redirect();
    }

    public function resolveUserFromCallback(): User
    {
        $socialiteUser = Socialite::driver('facebook')->user();
        if (! $socialiteUser->getId()) {
            throw new SocialAuthenticationException('Facebook did not return required account information.');
        }
        $this->ensureNoConflictingEmailUser($socialiteUser);
        $existingUser = $this->findExistingSocialiteUser($socialiteUser);
        $token = $this->exchangeToken($socialiteUser);
        $accounts = $this->getAccounts($socialiteUser->getId(), $token['access_token']);
        $this->ensureInstagramAccountsBelongToUser($existingUser, $accounts);
        $user = $this->createUpdateUser($socialiteUser);
        auth()->login($user);
        $accounts->each(function ($account) use ($user) {
            $user->instagramAccounts()->updateOrCreate([
                'instagram_user_id' => $account['instagram_user_id'],
            ], $account);
        });

        return $user;
    }

    protected function createUpdateUser($socialiteUser): User
    {
        return User::updateOrCreate([
            'socialite_user_type' => 'facebook',
            'socialite_user_id' => $socialiteUser->getId(),
        ], [
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
        ]);
    }

    protected function ensureNoConflictingEmailUser($socialiteUser): void
    {
        $existingUserByEmail = User::query()
            ->where('email', $socialiteUser->getEmail())
            ->first();
        if (
            $existingUserByEmail !== null
            && (
                $existingUserByEmail->socialite_user_type !== 'facebook'
                || $existingUserByEmail->socialite_user_id !== $socialiteUser->getId()
            )
        ) {
            throw new SocialAuthenticationException('A user with this email already exists.');
        }
    }

    protected function findExistingSocialiteUser($socialiteUser): ?User
    {
        return User::query()
            ->where('socialite_user_type', 'facebook')
            ->where('socialite_user_id', $socialiteUser->getId())
            ->first();
    }

    protected function ensureInstagramAccountsBelongToUser(?User $user, Collection $accounts): void
    {
        $instagramUserIds = $accounts
            ->pluck('instagram_user_id')
            ->filter()
            ->values();
        if ($instagramUserIds->isEmpty()) {
            return;
        }

        $conflictingAccount = InstagramAccount::query()
            ->whereIn('instagram_user_id', $instagramUserIds)
            ->when(
                $user,
                fn ($query) => $query->where('user_id', '!=', $user->id),
            )
            ->when(
                $user === null,
                fn ($query) => $query->whereNotNull('user_id'),
            )
            ->first();
        if ($conflictingAccount !== null) {
            throw new SocialAuthenticationException('One or more Instagram accounts are linked to a different user.');
        }
    }

    protected function exchangeToken($socialiteUser): array
    {
        return new Client($socialiteUser->token)->getLongLivedToken();
    }

    protected function getAccounts(string $id, string $token): Collection
    {
        return new Client($token, $id)->accounts();
    }
}
