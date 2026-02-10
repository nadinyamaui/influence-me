<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountType;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class InstagramAuthController
{
    /**
     * Redirect the user to Meta/Facebook OAuth for Instagram Graph access.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $intent = $request->string('intent')->toString();

        if (! in_array($intent, ['login', 'add_account'], true)) {
            $intent = 'login';
        }

        // Keep flow context outside OAuth state so Socialite can fully manage CSRF state checks.
        $request->session()->put('instagram_oauth_intent', $intent);

        return Socialite::driver('facebook')
            ->scopes([
                'instagram_basic',
                'instagram_manage_insights',
                'pages_show_list',
                'pages_read_engagement',
                'business_management',
            ])
            ->redirect();
    }

    /**
     * Handle the callback from Meta/Facebook OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        $intent = $request->session()->pull('instagram_oauth_intent', 'login');

        if (! is_string($intent) || ! in_array($intent, ['login', 'add_account'], true)) {
            $intent = 'login';
        }

        $failureRoute = $intent === 'add_account' ? 'dashboard' : 'login';

        if ($request->filled('error')) {
            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'instagram' => 'Instagram authorization was denied. Please try again.',
                ]);
        }

        try {
            $socialiteUser = Socialite::driver('facebook')->user();

            $tokenData = $this->exchangeForLongLivedToken((string) $socialiteUser->token);
            $longLivedToken = (string) data_get($tokenData, 'access_token');
            $expiresIn = (int) data_get($tokenData, 'expires_in', 0);

            if ($longLivedToken === '' || $expiresIn <= 0) {
                throw new \RuntimeException('Meta token exchange returned incomplete payload.');
            }

            $instagramProfile = $this->resolveInstagramProfileFromFacebookToken($longLivedToken);

            [$user] = DB::transaction(function () use ($socialiteUser, $instagramProfile, $longLivedToken, $expiresIn): array {
                $instagramUserId = (string) data_get($instagramProfile, 'id', '');

                if ($instagramUserId === '') {
                    throw new \RuntimeException('Instagram account resolution returned no user id.');
                }

                $username = $this->resolveUsername($instagramProfile);
                $name = (string) data_get($instagramProfile, 'name', $username);

                $account = InstagramAccount::query()
                    ->where('instagram_user_id', $instagramUserId)
                    ->first();

                if ($account instanceof InstagramAccount) {
                    $account->update([
                        'username' => $username,
                        'name' => $name,
                        'profile_picture_url' => data_get($instagramProfile, 'profile_picture_url'),
                        'access_token' => $longLivedToken,
                        'account_type' => $this->resolveAccountType($instagramProfile),
                        'token_expires_at' => now()->addSeconds($expiresIn),
                    ]);

                    return [$account->user];
                }

                $user = User::query()->create([
                    'name' => (string) ($socialiteUser->getName() ?: $name),
                    // Keep email nullable intent from RFC while supporting current schema constraints.
                    'email' => $socialiteUser->getEmail() ?: sprintf('%s@instagram.local', $instagramUserId),
                    'password' => null,
                ]);

                $account = InstagramAccount::query()->create([
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
                    'instagram_primary_account_id' => $account->id,
                ])->save();

                return [$user];
            });

            Auth::login($user, true);

            return redirect()->route('dashboard');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'instagram' => 'Unable to authenticate with Instagram right now. Ensure your Meta account has a linked Instagram professional account and try again.',
                ]);
        }
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
