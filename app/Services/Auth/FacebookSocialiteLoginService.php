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
        $user = auth()->user() ?? $this->createUpdateUser($socialiteUser);
        if (! auth()->check()) {
            auth()->login($user);
        }
        $token = $this->exchangeToken($socialiteUser);
        $this->getAccounts($socialiteUser->getId(), $token['access_token'])->each(function ($account) use ($user): void {
            $existingAccount = InstagramAccount::query()
                ->where('instagram_user_id', $account['instagram_user_id'])
                ->first();

            if ($existingAccount && $existingAccount->user_id !== $user->id) {
                throw new SocialAuthenticationException('This Instagram account is already linked to another user.');
            }

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

    protected function exchangeToken($socialiteUser): array
    {
        return new Client($socialiteUser->token)->getLongLivedToken();
    }

    protected function getAccounts(string $id, string $token): Collection
    {
        return new Client($token, $id)->accounts();
    }
}
