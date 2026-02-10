<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\InstagramOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
        $validated = $request->validate([
            'intent' => ['nullable', 'string', 'in:login,add_account'],
        ]);
        $intent = $validated['intent'] ?? 'login';

        // Keep flow context outside OAuth state so Socialite can fully manage CSRF state checks.
        $request->session()->put('instagram_oauth_intent', $intent);

        return $this->instagramOAuthService->redirectToProvider();
    }

    /**
     * Handle the callback from Meta/Facebook OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        $sessionIntent = (string) $request->session()->pull('instagram_oauth_intent', 'login');
        $intentValidator = Validator::make(
            ['intent' => $sessionIntent],
            ['intent' => ['required', 'string', 'in:login,add_account']]
        );
        $intent = $intentValidator->fails()
            ? 'login'
            : (string) $intentValidator->validated()['intent'];
        $failureRoute = $intent === 'add_account' ? 'dashboard' : 'login';

        if ($request->filled('error')) {
            return redirect()
                ->route($failureRoute)
                ->withErrors([
                    'instagram' => __('Instagram authorization was denied. Please try again.'),
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
                    'instagram' => __('Unable to authenticate with Instagram right now. Ensure your Meta account has a linked Instagram professional account and try again.'),
                ]);
        }
    }
}
