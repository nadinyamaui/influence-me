<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use App\Livewire\Clients\Create as ClientsCreate;
use App\Livewire\Clients\Edit as ClientsEdit;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientsShow;
use App\Livewire\Dashboard;
use App\Livewire\InstagramAccounts\Index as InstagramAccountsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/auth/facebook', [FacebookAuthController::class, 'redirect'])->middleware('guest')->name('auth.facebook');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('auth.facebook.callback');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/auth/facebook/add', [FacebookAuthController::class, 'addAccount'])->name('auth.facebook.add');

    Route::livewire('instagram-accounts', InstagramAccountsIndex::class)
        ->name('instagram-accounts.index');

    Route::livewire('clients', ClientsIndex::class)
        ->name('clients.index');

    Route::livewire('clients/create', ClientsCreate::class)
        ->name('clients.create');

    Route::livewire('clients/{client}/edit', ClientsEdit::class)
        ->name('clients.edit');

    Route::livewire('clients/{client}', ClientsShow::class)
        ->name('clients.show');

    Route::middleware(['verified'])->group(function (): void {
        Route::livewire('dashboard', Dashboard::class)
            ->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
