<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'hasLinkedInstagramAccount' => Auth::user()?->instagramAccounts()->exists() ?? false,
        ])->layout('layouts.app', [
            'title' => __('Dashboard'),
        ]);
    }
}
