<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\SocialAuthenticationException;
use App\Http\Controllers\Controller;
use App\Services\Auth\FacebookSocialiteLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class FacebookAuthController extends Controller
{
    public function __construct(
        private readonly FacebookSocialiteLoginService $loginService,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->put('facebook_auth_intent', 'login');

        return $this->loginService->redirectToProvider();
    }

    public function addAccount(Request $request): RedirectResponse
    {
        $request->session()->put('facebook_auth_intent', 'add_account');

        return $this->loginService->redirectToProvider();
    }

    public function callback(Request $request): RedirectResponse
    {
        $intent = $request->session()->pull('facebook_auth_intent', 'login');
        $isAddAccountFlow = $intent === 'add_account' && $request->user() !== null;

        try {
            if ($isAddAccountFlow) {
                $this->loginService->createSocialAccountsForLoggedUser();

                return redirect()
                    ->route('instagram-accounts.index')
                    ->with('status', 'Instagram accounts connected successfully.');
            }

            $this->loginService->createUserAndAccounts();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (SocialAuthenticationException $exception) {
            report($exception);

            if ($isAddAccountFlow) {
                return redirect()
                    ->route('instagram-accounts.index')
                    ->withErrors(['oauth' => $exception->getMessage()]);
            }

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            if ($isAddAccountFlow) {
                return redirect()
                    ->route('instagram-accounts.index')
                    ->withErrors(['oauth' => 'Unable to connect Instagram accounts. Please try again.']);
            }

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => 'Unable to complete Facebook sign in. Please try again.']);
        }
    }
}
