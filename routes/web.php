<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Dashboard;
use App\Livewire\InstagramAccounts\Index as InstagramAccountsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/auth/facebook', [FacebookAuthController::class, 'redirect'])->middleware('guest')->name('auth.facebook');
Route::get('/auth/facebook/add', [FacebookAuthController::class, 'addAccount'])->middleware('auth')->name('auth.facebook.add');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('auth.facebook.callback');

Route::livewire('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('instagram-accounts', InstagramAccountsIndex::class)
    ->middleware(['auth'])
    ->name('instagram-accounts.index');

Route::livewire('clients', ClientsIndex::class)
    ->middleware(['auth'])
    ->name('clients.index');

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
