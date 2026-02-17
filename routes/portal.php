<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Livewire\Portal\Analytics\Index as PortalAnalyticsIndex;
use App\Livewire\Portal\Dashboard as PortalDashboard;
use App\Livewire\Portal\Proposals\Index as PortalProposalsIndex;
use App\Livewire\Portal\Proposals\Show as PortalProposalsShow;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->middleware(['guest:client'])->group(function (): void {
    Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalAuthController::class, 'login'])
        ->middleware('throttle:client-portal-login')
        ->name('portal.login.store');
});

Route::prefix('portal')->middleware(['client.auth'])->group(function (): void {
    Route::livewire('/dashboard', PortalDashboard::class)->name('portal.dashboard');
    Route::livewire('/proposals', PortalProposalsIndex::class)->name('portal.proposals.index');
    Route::livewire('/proposals/{proposal}', PortalProposalsShow::class)->name('portal.proposals.show');
    Route::livewire('/analytics', PortalAnalyticsIndex::class)->name('portal.analytics.index');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');
});
