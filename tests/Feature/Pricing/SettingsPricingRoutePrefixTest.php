<?php

test('pricing routes use the settings prefix', function (): void {
    expect(route('pricing.products.index', absolute: false))->toBe('/settings/pricing/products');
    expect(route('pricing.plans.index', absolute: false))->toBe('/settings/pricing/plans');
    expect(route('pricing.tax-rates.index', absolute: false))->toBe('/settings/pricing/tax-rates');
});
