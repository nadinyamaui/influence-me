<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class FacebookAuthController extends Controller
{
    public function __construct(
        private readonly FacebookSocialiteLoginService $loginService,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        $state = $request->query('state');

        if (! is_string($state) || $state === '') {
            $state = null;
        }

        return $this->loginService->redirectToProvider($state);
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $authenticatedUser = Auth::guard('web')->user();

            if (! $authenticatedUser instanceof User) {
                $authenticatedUser = null;
            }

            $user = $this->loginService->resolveUserFromCallback($authenticatedUser);

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (SocialAuthenticationException $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => 'Unable to complete Facebook sign in. Please try again.']);
        }
    }
}
