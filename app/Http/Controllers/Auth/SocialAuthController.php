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

    public function redirect(Request $request): RedirectResponse
    {
        $driver = $this->resolveDriver(
            $request->route('driver'),
        );
        $request->session()->put('social_account_auth_intent', 'login');
        $this->loginService->useDriver($driver);

        return $this->loginService->redirectToProvider();
    }

    public function addAccount(Request $request): RedirectResponse
    {
        $driver = $this->resolveDriver(
            $request->route('driver'),
        );
        $request->session()->put('social_account_auth_intent', 'add_account');
        $this->loginService->useDriver($driver);

        return $this->loginService->redirectToProvider();
    }

    public function callback(Request $request): RedirectResponse
    {
        $driver = $this->resolveDriver(
            $request->route('driver'),
        );
        $this->loginService->useDriver($driver);
        $intent = $request->session()->pull('social_account_auth_intent', 'login');
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
                    ->withErrors(['oauth' => "Unable to connect {$this->loginService->driverLabel()} accounts. Please try again."]);
            }

            return redirect()
                ->route('login')
                ->withErrors(['oauth' => "Unable to complete {$this->loginService->driverLabel()} sign in. Please try again."]);
        }
    }

    private function resolveDriver(?string $driver): SocialNetwork
    {
        return SocialNetwork::tryFrom(strtolower($driver ?? '')) ?? SocialNetwork::Instagram;
    }
}
