<?php

use App\Http\Controllers\Portal\PortalAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->middleware(['guest:client'])->group(function (): void {
    Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalAuthController::class, 'login'])
        ->middleware('throttle:client-portal-login')
        ->name('portal.login.store');
});

Route::prefix('portal')->middleware(['auth:client'])->group(function (): void {
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');
});
