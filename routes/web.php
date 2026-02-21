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
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Pricing\Plans\Form as PricingPlansForm;
use App\Livewire\Pricing\Plans\Index as PricingPlansIndex;
use App\Livewire\Pricing\Products\Form as PricingProductsForm;
use App\Livewire\Pricing\Products\Index as PricingProductsIndex;
use App\Livewire\Proposals\Create as ProposalsCreate;
use App\Livewire\Proposals\Edit as ProposalsEdit;
use App\Livewire\Proposals\Index as ProposalsIndex;
use App\Livewire\Proposals\Show as ProposalsShow;
use App\Livewire\SocialAccounts\Index as SocialAccountsIndex;
use Illuminate\Support\Facades\Route;

$socialDriversPattern = implode(
    '|',
    array_map(
        static fn (SocialNetwork $network): string => $network->value,
        SocialNetwork::cases(),
    ),
);

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/auth/{driver}', [SocialAuthController::class, 'redirect'])
    ->where('driver', $socialDriversPattern)
    ->middleware('guest')
    ->name('auth.facebook');
Route::get('/auth/{driver}/callback', [SocialAuthController::class, 'callback'])
    ->where('driver', $socialDriversPattern)
    ->middleware('throttle:instagram-oauth-callback')
    ->name('auth.facebook.callback');

Route::middleware(['auth'])->group(function () use ($socialDriversPattern): void {
    Route::get('/auth/{driver}/add', [SocialAuthController::class, 'addAccount'])
        ->where('driver', $socialDriversPattern)
        ->name('auth.facebook.add');

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

    Route::livewire('pricing/products', PricingProductsIndex::class)
        ->name('pricing.products.index');

    Route::livewire('pricing/products/create', PricingProductsForm::class)
        ->name('pricing.products.create');

    Route::livewire('pricing/products/{product}/edit', PricingProductsForm::class)
        ->name('pricing.products.edit');

    Route::livewire('pricing/plans', PricingPlansIndex::class)
        ->name('pricing.plans.index');

    Route::livewire('pricing/plans/create', PricingPlansForm::class)
        ->name('pricing.plans.create');

    Route::livewire('pricing/plans/{plan}/edit', PricingPlansForm::class)
        ->name('pricing.plans.edit');

    Route::middleware(['verified'])->group(function (): void {
        Route::livewire('dashboard', Dashboard::class)
            ->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
