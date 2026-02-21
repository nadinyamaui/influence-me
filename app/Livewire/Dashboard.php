<?php

namespace App\Livewire;

use App\Enums\MediaType;
use App\Services\Instagram\InstagramClient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        /*
        $account = Auth::user()?->socialAccounts()->first();
        $client = app(InstagramClient::class, [
            'user_id' => $account->social_network_user_id,
            'access_token' => $account->access_token,
        ]);
        dd($client->getAudienceDemographics('17841450689131511', MediaType::Post));*/

        return view('livewire.dashboard', [
            'hasLinkedSocialAccount' => Auth::user()?->socialAccounts()->exists() ?? false,
        ])->layout('layouts.app', [
            'title' => __('Dashboard'),
        ]);
    }
}
