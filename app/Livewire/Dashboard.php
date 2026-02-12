<?php

namespace App\Livewire;

use App\Enums\MediaType;
use App\Services\Facebook\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        /*
        $account = Auth::user()?->instagramAccounts()->first();
        $client = app(Client::class, [
            'user_id' => $account->instagram_user_id,
            'access_token' => $account->access_token,
        ]);
        dd($client->getAudienceDemographics('17841450689131511', MediaType::Post));*/

        return view('livewire.dashboard', [
            'hasLinkedInstagramAccount' => Auth::user()?->instagramAccounts()->exists() ?? false,
        ])->layout('layouts.app', [
            'title' => __('Dashboard'),
        ]);
    }
}
