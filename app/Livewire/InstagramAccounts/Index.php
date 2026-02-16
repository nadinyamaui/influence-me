<?php

namespace App\Livewire\InstagramAccounts;

use App\Enums\SyncStatus;
use App\Jobs\SyncAllInstagramData;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    public function syncNow(int $accountId): void
    {
        $account = User::resolveInstagramAccount($accountId);
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
        $account = User::resolveInstagramAccount($accountId);
        $this->authorize('update', $account);

        Auth::user()->instagramAccounts()->update(['is_primary' => false]);
        $account->update(['is_primary' => true]);

        session()->flash('status', 'Primary Instagram account updated.');
    }

    public function disconnect(int $accountId): void
    {
        $account = User::resolveInstagramAccount($accountId);
        $this->authorize('delete', $account);

        if (Auth::user()->instagramAccounts()->count() <= 1) {
            $this->addError('disconnect', 'You cannot disconnect your last Instagram account.');

            return;
        }

        $this->resetErrorBag('disconnect');

        $wasPrimary = $account->is_primary;
        $account->delete();

        if ($wasPrimary) {
            $nextAccount = Auth::user()->instagramAccounts()
                ->orderBy('id')
                ->first();

            $nextAccount?->update(['is_primary' => true]);
        }

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

    private function accounts(): Collection
    {
        return Auth::user()->instagramAccounts()
            ->orderByDesc('is_primary')
            ->orderBy('username')
            ->get();
    }
}
