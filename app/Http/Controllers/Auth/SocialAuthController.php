<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialNetwork;
use App\Exceptions\Auth\SocialAuthenticationException;
use App\Http\Controllers\Controller;
use App\Services\Auth\SocialiteLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialiteLoginService $loginService,
    ) {}

    public function redirect(Request $request, SocialNetwork $provider): RedirectResponse
    {
        if (! $provider->supportsLogin()) {
            throw new SocialAuthenticationException("{$provider->label()} login is not supported. Connect {$provider->label()} after logging in.");
        }

        $request->session()->put('social_account_auth_intent', 'login');

        return $this->loginService->usingDriver($provider)->redirectToProvider();
    }

    public function addAccount(Request $request, SocialNetwork $provider): RedirectResponse
    {
        $request->session()->put('social_account_auth_intent', 'add_account');
        $request->session()->put(
            'social_account_redirect_route',
            $provider === SocialNetwork::Tiktok ? 'tiktok-accounts.index' : 'instagram-accounts.index',
        );

        return $this->loginService->usingDriver($provider)->redirectToProvider();
    }

    public function callback(Request $request, SocialNetwork $provider): RedirectResponse
    {
        $loginService = $this->loginService->usingDriver($provider);
        $intent = $request->session()->pull('social_account_auth_intent', 'login');
        $redirectRoute = $request->session()->pull(
            'social_account_redirect_route',
            $provider === SocialNetwork::Tiktok ? 'tiktok-accounts.index' : 'instagram-accounts.index',
        );
        $isAddAccountFlow = $intent === 'add_account' && $request->user() !== null;

        try {
            if ($isAddAccountFlow) {
                $loginService->createSocialAccountsForLoggedUser();

                return redirect()
                    ->route($redirectRoute)
                    ->with('status', "{$provider->label()} accounts connected successfully.");
            }

            $loginService->createUserAndAccounts();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (SocialAuthenticationException $exception) {
            report($exception);

            if ($isAddAccountFlow) {
                return redirect()
                    ->route($redirectRoute)
                    ->withErrors(['oauth' => $exception->getMessage()]);
            }

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            if ($isAddAccountFlow) {
                return redirect()
                    ->route($redirectRoute)
                    ->withErrors(['oauth' => "Unable to connect {$loginService->driverLabel()} accounts. Please try again."]);
            }

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => "Unable to complete {$loginService->driverLabel()} sign in. Please try again."]);
        }
    }
}
