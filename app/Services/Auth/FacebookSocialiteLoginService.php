<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        $user = User::updateOrCreate([
            'socialite_user_type' => 'facebook',
            'socialite_user_id' => $socialiteUser->getId(),
        ], [
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
        ]);
        auth()->login($user);

        return $user;
    }
}
