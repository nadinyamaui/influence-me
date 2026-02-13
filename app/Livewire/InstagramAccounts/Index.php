<?php

namespace App\Livewire\InstagramAccounts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
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
        return Auth::user()?->instagramAccounts()
            ->orderByDesc('is_primary')
            ->orderBy('username')
            ->get() ?? collect();
    }
}
