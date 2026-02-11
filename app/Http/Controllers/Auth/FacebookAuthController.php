<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Throwable;

class FacebookAuthController extends Controller
{
    public function __construct(
        private readonly FacebookSocialiteLoginService $loginService,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        return $this->loginService->redirectToProvider();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $user = $this->loginService->resolveUserFromCallback();

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
