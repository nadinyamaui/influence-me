<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\InstagramOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class InstagramAuthController
{
    public function __construct(
        private readonly InstagramOAuthService $instagramOAuthService,
    ) {}

    /**
     * Redirect the user to Meta/Facebook OAuth for Instagram Graph access.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $intent = $this->instagramOAuthService->normalizeIntent(
            $request->string('intent')->toString()
        );

        // Keep flow context outside OAuth state so Socialite can fully manage CSRF state checks.
        $request->session()->put('instagram_oauth_intent', $intent);

        return Socialite::driver('facebook')
            ->scopes($this->instagramOAuthService->oauthScopes())
            ->redirect();
    }

    /**
     * Handle the callback from Meta/Facebook OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        $intent = $this->instagramOAuthService->normalizeIntent(
            (string) $request->session()->pull('instagram_oauth_intent', 'login')
        );
        $failureRoute = $this->instagramOAuthService->failureRouteForIntent($intent);

        if ($request->filled('error')) {
            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'instagram' => 'Instagram authorization was denied. Please try again.',
                ]);
        }

        try {
            $result = $this->instagramOAuthService->processCallback(
                intent: $intent,
                currentUser: Auth::user(),
            );

            if ($result->shouldLogin) {
                Auth::login($result->user, true);
            }

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
}
