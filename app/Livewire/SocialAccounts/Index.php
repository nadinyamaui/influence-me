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
        $this->provider = SocialNetwork::fromProviderOrFail($provider)->value;
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

        Auth::user()->socialAccounts()
            ->where('social_network', SocialNetwork::fromProviderOrFail($this->provider))
            ->update(['is_primary' => false]);
        $account->update(['is_primary' => true]);

        session()->flash('status', 'Primary '.SocialNetwork::fromProviderOrFail($this->provider)->label().' account updated.');
    }

    public function disconnect(int $accountId): void
    {
        $account = $this->resolveProviderAccount($accountId);
        $this->authorize('delete', $account);

        $this->resetErrorBag('disconnect');

        $wasPrimary = $account->is_primary;
        $account->delete();

        if ($wasPrimary) {
            $nextAccount = Auth::user()->socialAccounts()
                ->where('social_network', SocialNetwork::fromProviderOrFail($this->provider))
                ->orderBy('id')
                ->first();

            $nextAccount?->update(['is_primary' => true]);
        }

        session()->flash('status', SocialNetwork::fromProviderOrFail($this->provider)->label().' account disconnected.');
    }

    public function render()
    {
        $providerNetwork = SocialNetwork::fromProviderOrFail($this->provider);

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
            ->where('social_network', SocialNetwork::fromProviderOrFail($this->provider))
            ->orderByDesc('is_primary')
            ->orderBy('username')
            ->get();
    }

    private function resolveProviderAccount(int $accountId): SocialAccount
    {
        $account = User::resolveSocialAccount($accountId);

        abort_if($account->social_network !== SocialNetwork::fromProviderOrFail($this->provider), 404);

        return $account;
    }
}
