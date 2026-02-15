<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use App\Livewire\Clients\Create as ClientsCreate;
use App\Livewire\Clients\Edit as ClientsEdit;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientsShow;
use App\Livewire\Content\Index as ContentIndex;
use App\Livewire\Dashboard;
use App\Livewire\InstagramAccounts\Index as InstagramAccountsIndex;
use App\Livewire\Proposals\Create as ProposalsCreate;
use App\Livewire\Proposals\Edit as ProposalsEdit;
use App\Livewire\Proposals\Index as ProposalsIndex;
use App\Livewire\Proposals\Show as ProposalsShow;
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

    Route::livewire('content', ContentIndex::class)
        ->name('content.index');

    Route::livewire('clients', ClientsIndex::class)
        ->name('clients.index');

    Route::livewire('clients/create', ClientsCreate::class)
        ->name('clients.create');

    Route::livewire('clients/{client}/edit', ClientsEdit::class)
        ->name('clients.edit');

    Route::livewire('clients/{client}', ClientsShow::class)
        ->name('clients.show');

    Route::livewire('proposals', ProposalsIndex::class)
        ->name('proposals.index');

    Route::livewire('proposals/create', ProposalsCreate::class)
        ->name('proposals.create');

    Route::livewire('proposals/{proposal}/edit', ProposalsEdit::class)
        ->name('proposals.edit');

    Route::livewire('proposals/{proposal}', ProposalsShow::class)
        ->name('proposals.show');

    Route::middleware(['verified'])->group(function (): void {
        Route::livewire('dashboard', Dashboard::class)
            ->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
