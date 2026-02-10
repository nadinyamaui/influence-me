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
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class InstagramAuthController
{
    /**
     * Redirect the user to Instagram OAuth.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $intent = $request->string('intent')->toString();

        if (! in_array($intent, ['login', 'add_account'], true)) {
            $intent = 'login';
        }

        // Keep flow context outside OAuth state so Socialite can fully manage CSRF state checks.
        $request->session()->put('instagram_oauth_intent', $intent);

        return Socialite::driver('instagram')
            ->scopes([
                'instagram_basic',
                'instagram_manage_insights',
                'pages_show_list',
                'pages_read_engagement',
            ])
            ->redirect();
    }

    /**
     * Handle the callback from Instagram OAuth.
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
            $socialiteUser = Socialite::driver('instagram')->user();

            $tokenData = $this->exchangeForLongLivedToken((string) $socialiteUser->token);
            $longLivedToken = (string) data_get($tokenData, 'access_token');
            $expiresIn = (int) data_get($tokenData, 'expires_in', 0);

            if ($longLivedToken === '' || $expiresIn <= 0) {
                throw new \RuntimeException('Instagram token exchange returned incomplete payload.');
            }

            [$user] = DB::transaction(function () use ($socialiteUser, $longLivedToken, $expiresIn): array {
                $instagramUserId = (string) $socialiteUser->getId();

                $account = InstagramAccount::query()
                    ->where('instagram_user_id', $instagramUserId)
                    ->first();

                if ($account instanceof InstagramAccount) {
                    $account->update([
                        'username' => $this->resolveUsername($socialiteUser),
                        'name' => $socialiteUser->getName(),
                        'access_token' => $longLivedToken,
                        'token_expires_at' => now()->addSeconds($expiresIn),
                    ]);

                    return [$account->user];
                }

                $user = User::query()->create([
                    'name' => $socialiteUser->getName() ?: $this->resolveUsername($socialiteUser),
                    // Keep email nullable intent from RFC while supporting current schema constraints.
                    'email' => $socialiteUser->getEmail() ?: sprintf('%s@instagram.local', $instagramUserId),
                    'password' => null,
                ]);

                $account = InstagramAccount::query()->create([
                    'user_id' => $user->id,
                    'instagram_user_id' => $instagramUserId,
                    'username' => $this->resolveUsername($socialiteUser),
                    'name' => $socialiteUser->getName(),
                    'account_type' => $this->resolveAccountType($socialiteUser),
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
                    'instagram' => 'Unable to authenticate with Instagram right now. Please try again.',
                ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortLivedToken,
        ]);

        $response->throw();

        return $response->json();
    }

    private function resolveUsername(object $socialiteUser): string
    {
        $nickname = (string) ($socialiteUser->getNickname() ?: data_get($socialiteUser->user, 'username'));

        if ($nickname !== '') {
            return $nickname;
        }

        $name = (string) $socialiteUser->getName();

        return Str::of($name)->slug('_')->toString() ?: 'instagram_user';
    }

    private function resolveAccountType(object $socialiteUser): AccountType
    {
        $value = strtolower((string) data_get($socialiteUser->user, 'account_type', 'creator'));

        return $value === AccountType::Business->value
            ? AccountType::Business
            : AccountType::Creator;
    }
}
