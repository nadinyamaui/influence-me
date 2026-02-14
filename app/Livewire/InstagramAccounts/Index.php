<?php

namespace App\Livewire\InstagramAccounts;

use App\Enums\SyncStatus;
use App\Jobs\SyncAllInstagramData;
use App\Models\InstagramAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public ?int $disconnectingAccountId = null;

    public function syncNow(int $accountId): void
    {
        $account = $this->resolveUserAccount($accountId);
        $this->authorize('update', $account);

        if ($account->sync_status === SyncStatus::Syncing) {
            return;
        }

        $account->update([
            'sync_status' => SyncStatus::Syncing,
            'last_sync_error' => null,
        ]);

        SyncAllInstagramData::dispatch($account);
    }

    public function setPrimary(int $accountId): void
    {
        $account = $this->resolveUserAccount($accountId);
        $this->authorize('update', $account);

        $user = Auth::user();
        if ($user === null) {
            abort(403);
        }

        $user->instagramAccounts()->update(['is_primary' => false]);
        $account->update(['is_primary' => true]);

        session()->flash('status', 'Primary Instagram account updated.');
    }

    public function confirmDisconnect(int $accountId): void
    {
        $account = $this->resolveUserAccount($accountId);
        $this->authorize('view', $account);

        $user = Auth::user();
        if ($user === null) {
            abort(403);
        }

        if ($user->instagramAccounts()->count() <= 1) {
            $this->addError('disconnect', 'You cannot disconnect your last Instagram account.');
            $this->disconnectingAccountId = null;

            return;
        }

        $this->resetErrorBag('disconnect');
        $this->disconnectingAccountId = $account->id;
    }

    public function cancelDisconnect(): void
    {
        $this->disconnectingAccountId = null;
    }

    public function disconnect(): void
    {
        if ($this->disconnectingAccountId === null) {
            return;
        }

        $account = $this->resolveUserAccount($this->disconnectingAccountId);
        $this->authorize('delete', $account);

        $wasPrimary = $account->is_primary;
        $account->delete();

        if ($wasPrimary) {
            $nextAccount = Auth::user()?->instagramAccounts()
                ->orderBy('id')
                ->first();

            $nextAccount?->update(['is_primary' => true]);
        }

        $this->disconnectingAccountId = null;
        session()->flash('status', 'Instagram account disconnected.');
    }

    public function render()
    {
        return view('pages.instagram-accounts.index', [
            'accounts' => $this->accounts(),
        ])->layout('layouts.app', [
            'title' => __('Instagram Accounts'),
        ]);
    }

    public function disconnectingAccount(): ?InstagramAccount
    {
        if ($this->disconnectingAccountId === null) {
            return null;
        }

        return Auth::user()?->instagramAccounts()
            ->whereKey($this->disconnectingAccountId)
            ->first();
    }

    private function accounts(): Collection
    {
        return Auth::user()?->instagramAccounts()
            ->orderByDesc('is_primary')
            ->orderBy('username')
            ->get() ?? collect();
    }

    private function resolveUserAccount(int $accountId): InstagramAccount
    {
        $account = Auth::user()?->instagramAccounts()
            ->whereKey($accountId)
            ->first();

        if ($account === null) {
            abort(404);
        }

        return $account;
    }
}
