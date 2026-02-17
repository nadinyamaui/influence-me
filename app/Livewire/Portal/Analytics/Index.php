<?php

namespace App\Livewire\Portal\Analytics;

use App\Models\Client;
use App\Services\Clients\ClientAnalyticsService;
use App\Services\Clients\ClientAudienceDemographicsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $client = $this->authenticatedClient();

        return view('pages.portal.analytics.index', [
            'client' => $client,
            'clientAnalytics' => app(ClientAnalyticsService::class)->build($client),
            'audienceDemographics' => app(ClientAudienceDemographicsService::class)->build($client),
        ])->layout('layouts.portal', [
            'title' => __('Analytics'),
        ]);
    }

    private function authenticatedClient(): Client
    {
        $client = Auth::guard('client')->user()?->client;

        if (! $client instanceof Client) {
            abort(403);
        }

        return $client;
    }
}
