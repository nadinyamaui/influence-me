<?php

use App\Livewire\Pricing\Plans\Form as PricingPlansForm;
use App\Livewire\Pricing\Plans\Index as PricingPlansIndex;
use App\Livewire\Pricing\Products\Form as PricingProductsForm;
use App\Livewire\Pricing\Products\Index as PricingProductsIndex;
use App\Livewire\Pricing\TaxRates\Form as TaxRatesForm;
use App\Livewire\Pricing\TaxRates\Index as TaxRatesIndex;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');

    Route::livewire('settings/pricing/products', PricingProductsIndex::class)
        ->name('pricing.products.index');

    Route::livewire('settings/pricing/products/create', PricingProductsForm::class)
        ->name('pricing.products.create');

    Route::livewire('settings/pricing/products/{product}/edit', PricingProductsForm::class)
        ->name('pricing.products.edit');

    Route::livewire('settings/pricing/plans', PricingPlansIndex::class)
        ->name('pricing.plans.index');

    Route::livewire('settings/pricing/plans/create', PricingPlansForm::class)
        ->name('pricing.plans.create');

    Route::livewire('settings/pricing/plans/{plan}/edit', PricingPlansForm::class)
        ->name('pricing.plans.edit');

    Route::livewire('settings/pricing/tax-rates', TaxRatesIndex::class)
        ->name('pricing.tax-rates.index');

    Route::livewire('settings/pricing/tax-rates/create', TaxRatesForm::class)
        ->name('pricing.tax-rates.create');

    Route::livewire('settings/pricing/tax-rates/{taxRate}/edit', TaxRatesForm::class)
        ->name('pricing.tax-rates.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/password', 'pages::settings.password')->name('user-password.edit');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/two-factor', 'pages::settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
