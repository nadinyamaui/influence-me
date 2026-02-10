<?php

namespace App\Http\Controllers\Auth;

use App\Enums\InstagramOAuthIntent;
use App\Services\Auth\InstagramOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class InstagramAuthController
{
    public function __construct(
        private readonly InstagramOAuthService $instagramOAuthService,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intent' => ['nullable', Rule::enum(InstagramOAuthIntent::class)],
        ]);
        $intent = InstagramOAuthIntent::tryFrom((string) ($validated['intent'] ?? ''))
            ?? InstagramOAuthIntent::Login;
        if ($intent === InstagramOAuthIntent::AddAccount && ! Auth::check()) {
            $intent = InstagramOAuthIntent::Login;
        }

        $request->session()->put('instagram_oauth_intent', $intent->value);

        return $this->instagramOAuthService->redirectToProvider();
    }

    public function callback(Request $request): RedirectResponse
    {
        $sessionIntent = (string) $request->session()->pull('instagram_oauth_intent', InstagramOAuthIntent::Login->value);
        $intentValidator = Validator::make(
            ['intent' => $sessionIntent],
            ['intent' => ['required', Rule::enum(InstagramOAuthIntent::class)]]
        );
        $intent = $intentValidator->fails()
            ? InstagramOAuthIntent::Login
            : InstagramOAuthIntent::from((string) $intentValidator->validated()['intent']);
        $failureRoute = $intent->failureRoute();

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
