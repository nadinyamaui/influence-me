<?php

use App\Enums\SocialNetwork;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Clients\Create as ClientsCreate;
use App\Livewire\Clients\Edit as ClientsEdit;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientsShow;
use App\Livewire\Content\Index as ContentIndex;
use App\Livewire\Dashboard;
use App\Livewire\Invoices\Form as InvoicesForm;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoicesShow;
use App\Livewire\Proposals\Create as ProposalsCreate;
use App\Livewire\Proposals\Edit as ProposalsEdit;
use App\Livewire\Proposals\Index as ProposalsIndex;
use App\Livewire\Proposals\Show as ProposalsShow;
use App\Livewire\SocialAccounts\Index as SocialAccountsIndex;
use Illuminate\Support\Facades\Route;

$socialProviders = array_map(
    static fn (SocialNetwork $network): string => $network->value,
    SocialNetwork::cases(),
);

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::prefix('/auth')->group(function () use ($socialProviders): void {
    Route::get('{provider}', [SocialAuthController::class, 'redirect'])
        ->middleware('guest')
        ->whereIn('provider', $socialProviders)
        ->name('social.auth');
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])
        ->middleware('throttle:instagram-oauth-callback')
        ->whereIn('provider', $socialProviders)
        ->name('social.callback');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/auth/instagram/add', [SocialAuthController::class, 'addAccount'])->name('auth.instagram.add');

    Route::livewire('instagram-accounts', SocialAccountsIndex::class)
        ->name('instagram-accounts.index');

    Route::livewire('content', ContentIndex::class)
        ->name('content.index');

    Route::livewire('analytics', AnalyticsIndex::class)
        ->name('analytics.index');

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

    Route::livewire('invoices', InvoicesIndex::class)
        ->name('invoices.index');

    Route::livewire('invoices/create', InvoicesForm::class)
        ->name('invoices.create');

    Route::livewire('invoices/{invoice}/edit', InvoicesForm::class)
        ->name('invoices.edit');

    Route::livewire('invoices/{invoice}', InvoicesShow::class)
        ->name('invoices.show');

    Route::middleware(['verified'])->group(function (): void {
        Route::livewire('dashboard', Dashboard::class)
            ->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
