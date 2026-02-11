<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;

class FacebookSocialiteLoginService
{
    /**
     * @var list<string>
     */
    private array $scopes = [
        'instagram_basic',
        'instagram_manage_insights',
        'pages_show_list',
        'pages_read_engagement',
    ];

    public function redirectToProvider(?string $state = null): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->scopes($this->scopes)
            ->with(['state' => $state ?? 'login'])
            ->redirect();
    }

    public function resolveUserFromCallback(?User $authenticatedUser = null): User
    {
        $socialiteUser = Socialite::driver('facebook')->user();

        if ((string) $socialiteUser->getId() === '') {
            throw new SocialAuthenticationException('Facebook did not return required account information.');
        }

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        return $this->findOrCreateInfluencerUser($socialiteUser);
    }

    private function findOrCreateInfluencerUser(SocialiteUserContract $socialiteUser): User
    {
        $email = $socialiteUser->getEmail();

        if (! is_string($email) || $email === '') {
            throw new SocialAuthenticationException('Facebook did not return an email address for sign in.');
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null) {
            return $existingUser;
        }

        return User::query()->create([
            'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'Instagram User',
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
        ]);
    }
}
