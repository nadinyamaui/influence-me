<?php

namespace App\Livewire\SocialAccounts;

use App\Enums\SocialNetwork;
use App\Enums\SyncStatus;
use App\Jobs\SyncAllSocialMediaData;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public string $provider;

    public function mount(string $provider): void
    {
        $providerNetwork = SocialNetwork::tryFrom($provider);

        abort_if($providerNetwork === null, 404);

        $this->provider = $providerNetwork->value;
    }

    public function syncNow(int $accountId): void
    {
        $account = $this->resolveProviderAccount($accountId);
        $this->authorize('update', $account);

        if ($account->sync_status === SyncStatus::Syncing) {
            return;
        }

        $account->update([
            'sync_status' => SyncStatus::Syncing,
            'last_sync_error' => null,
        ]);

        SyncAllSocialMediaData::dispatch($account);
    }

    public function setPrimary(int $accountId): void
    {
        $account = $this->resolveProviderAccount($accountId);
        $this->authorize('update', $account);

        Auth::user()->socialAccounts()->update(['is_primary' => false]);
        $account->update(['is_primary' => true]);

        session()->flash('status', "Primary {$this->providerNetwork()->label()} account updated.");
    }

    public function disconnect(int $accountId): void
    {
        $account = $this->resolveProviderAccount($accountId);
        $this->authorize('delete', $account);

        if (Auth::user()->socialAccounts()->where('social_network', $this->providerNetwork())->count() <= 1) {
            $this->addError('disconnect', "You cannot disconnect your last {$this->providerNetwork()->label()} account.");

            return;
        }

        $this->resetErrorBag('disconnect');

        $wasPrimary = $account->is_primary;
        $account->delete();

        if ($wasPrimary) {
            $nextAccount = Auth::user()->socialAccounts()
                ->where('social_network', $this->providerNetwork())
                ->orderBy('id')
                ->first();

            $nextAccount?->update(['is_primary' => true]);
        }

        session()->flash('status', "{$this->providerNetwork()->label()} account disconnected.");
    }

    public function render()
    {
        $providerNetwork = $this->providerNetwork();

        return view('pages.social-accounts.index', [
            'accounts' => $this->accounts(),
            'providerNetwork' => $providerNetwork,
        ])->layout('layouts.app', [
            'title' => __($providerNetwork->label().' Accounts'),
        ]);
    }

    private function accounts(): Collection
    {
        return Auth::user()->socialAccounts()
            ->where('social_network', $this->providerNetwork())
            ->orderByDesc('is_primary')
            ->orderBy('username')
            ->get();
    }

    private function providerNetwork(): SocialNetwork
    {
        $providerNetwork = SocialNetwork::tryFrom($this->provider);

        abort_if($providerNetwork === null, 404);

        return $providerNetwork;
    }

    private function resolveProviderAccount(int $accountId): SocialAccount
    {
        $account = User::resolveSocialAccount($accountId);

        abort_if($account->social_network !== $this->providerNetwork(), 404);

        return $account;
    }
}
