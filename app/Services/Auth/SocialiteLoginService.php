<?php

namespace App\Services\Auth;

use App\Enums\SocialNetwork;
use App\Exceptions\Auth\SocialAuthenticationException;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialMedia\Instagram\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Laravel\Socialite\Facades\Socialite;

class SocialiteLoginService
{
    private SocialNetwork $driver = SocialNetwork::Instagram;

    public function usingDriver(SocialNetwork $driver): self
    {
        $service = clone $this;
        $service->driver = $driver;

        return $service;
    }

    public function driverLabel(): string
    {
        return $this->driver->label();
    }

    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver($this->driver->socialiteDriver())
            ->scopes($this->driver->oauthScopes())
            ->redirect();
    }

    public function createUserAndAccounts(): User
    {
        $socialiteUser = Socialite::driver($this->driver->socialiteDriver())->user();
        if (! $socialiteUser->getId()) {
            throw new SocialAuthenticationException("{$this->driverLabel()} did not return required account information.");
        }
        $this->ensureNoConflictingEmailUser($socialiteUser);
        $existingUser = $this->findExistingSocialiteUser($socialiteUser);
        $token = $this->exchangeToken($socialiteUser);
        $accounts = $this->getAccounts($socialiteUser->getId(), $token['access_token']);
        $this->ensureSocialAccountsBelongToUser($existingUser, $accounts);
        $user = $this->createUpdateUser($socialiteUser);
        auth()->login($user);
        $this->upsertSocialAccounts($accounts, $user);

        return $user;
    }

    public function createSocialAccountsForLoggedUser(): User
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            throw new SocialAuthenticationException("You must be logged in to link {$this->driverLabel()} accounts.");
        }

        $socialiteUser = Socialite::driver($this->driver->socialiteDriver())->user();
        if (! $socialiteUser->getId()) {
            throw new SocialAuthenticationException("{$this->driverLabel()} did not return required account information.");
        }

        $token = $this->exchangeToken($socialiteUser);
        $accounts = $this->getAccounts($socialiteUser->getId(), $token['access_token']);
        $this->ensureSocialAccountsBelongToUser($user, $accounts);
        $this->upsertSocialAccounts($accounts, $user);

        return $user;
    }

    protected function createUpdateUser($socialiteUser): User
    {
        return User::updateOrCreate([
            'socialite_user_type' => $this->driver->socialiteDriver(),
            'socialite_user_id' => $socialiteUser->getId(),
        ], [
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
        ]);
    }

    protected function ensureNoConflictingEmailUser($socialiteUser): void
    {
        $existingUserByEmail = User::query()
            ->where('email', $socialiteUser->getEmail())
            ->first();
        if (
            $existingUserByEmail !== null
            && (
                $existingUserByEmail->socialite_user_type !== $this->driver->socialiteDriver()
                || $existingUserByEmail->socialite_user_id !== $socialiteUser->getId()
            )
        ) {
            throw new SocialAuthenticationException('A user with this email already exists.');
        }
    }

    protected function findExistingSocialiteUser($socialiteUser): ?User
    {
        return User::query()
            ->where('socialite_user_type', $this->driver->socialiteDriver())
            ->where('socialite_user_id', $socialiteUser->getId())
            ->first();
    }

    protected function ensureSocialAccountsBelongToUser(?User $user, Collection $accounts): void
    {
        $socialNetworkUserIds = $accounts
            ->filter(
                fn (array $account): bool => ($account['social_network'] ?? SocialNetwork::Instagram->value) === SocialNetwork::Instagram->value
            )
            ->pluck('social_network_user_id')
            ->filter()
            ->values();
        if ($socialNetworkUserIds->isEmpty()) {
            return;
        }

        $conflictingAccount = SocialAccount::query()
            ->where('social_network', SocialNetwork::Instagram->value)
            ->whereIn('social_network_user_id', $socialNetworkUserIds)
            ->when(
                $user,
                fn ($query) => $query->where('user_id', '!=', $user->id),
            )
            ->when(
                $user === null,
                fn ($query) => $query->whereNotNull('user_id'),
            )
            ->first();
        if ($conflictingAccount !== null) {
            throw new SocialAuthenticationException("One or more {$this->driverLabel()} accounts are linked to a different user.");
        }
    }

    protected function exchangeToken($socialiteUser): array
    {
        return new Client($socialiteUser->token)->getLongLivedToken();
    }

    protected function getAccounts(string $id, string $token): Collection
    {
        return new Client($token, $id)->accounts();
    }

    protected function upsertSocialAccounts($accounts, $user): void
    {
        $accounts->each(function ($account) use ($user) {
            $user->socialAccounts()->updateOrCreate([
                'social_network' => $account['social_network'] ?? SocialNetwork::Instagram->value,
                'social_network_user_id' => $account['social_network_user_id'],
            ], $account);
        });
    }

}
