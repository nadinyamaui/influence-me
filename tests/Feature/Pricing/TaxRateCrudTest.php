<?php

use App\Livewire\Pricing\TaxRates\Form as TaxRatesForm;
use App\Livewire\Pricing\TaxRates\Index as TaxRatesIndex;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are redirected from tax rate pages', function (): void {
    $taxRate = TaxRate::factory()->create();

    $this->get(route('pricing.tax-rates.index'))->assertRedirect(route('login'));
    $this->get(route('pricing.tax-rates.create'))->assertRedirect(route('login'));
    $this->get(route('pricing.tax-rates.edit', $taxRate))->assertRedirect(route('login'));
});

test('authenticated influencers can view their tax rates list', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    TaxRate::factory()->for($user)->create(['label' => 'Owner Tax']);
    TaxRate::factory()->for($outsider)->create(['label' => 'Hidden Tax']);

    $this->actingAs($user)
        ->get(route('pricing.tax-rates.index'))
        ->assertSuccessful()
        ->assertSee('Tax Rates')
        ->assertSee('Owner Tax')
        ->assertDontSee('Hidden Tax')
        ->assertSee('href="'.route('pricing.tax-rates.index').'"', false);
});

test('tax rates index supports search status and sort filters through builder methods', function (): void {
    $user = User::factory()->create();

    TaxRate::factory()->for($user)->create([
        'label' => 'Z Rate',
        'rate' => 15,
        'is_active' => true,
    ]);

    TaxRate::factory()->for($user)->create([
        'label' => 'A Rate',
        'rate' => 5,
        'is_active' => true,
    ]);

    TaxRate::factory()->for($user)->create([
        'label' => 'Inactive Rate',
        'rate' => 25,
        'is_active' => false,
    ]);

    Livewire::actingAs($user)
        ->test(TaxRatesIndex::class)
        ->set('search', 'rate')
        ->set('status', 'active')
        ->set('sort', 'label_asc')
        ->assertSeeInOrder(['A Rate', 'Z Rate'])
        ->assertDontSee('Inactive Rate')
        ->set('status', 'inactive')
        ->assertSee('Inactive Rate')
        ->assertDontSee('A Rate')
        ->set('sort', 'rate_desc')
        ->assertSee('Inactive Rate');
});

test('tax rates index normalizes invalid filter values', function (): void {
    $user = User::factory()->create();

    TaxRate::factory()->for($user)->create([
        'label' => 'Active Tax',
        'is_active' => true,
    ]);

    TaxRate::factory()->for($user)->create([
        'label' => 'Inactive Tax',
        'is_active' => false,
    ]);

    Livewire::actingAs($user)
        ->test(TaxRatesIndex::class)
        ->set('status', 'invalid-status')
        ->assertSet('status', 'active')
        ->set('sort', 'invalid-sort')
        ->assertSet('sort', 'newest')
        ->assertSee('Active Tax')
        ->assertDontSee('Inactive Tax');
});

test('tax rates list shows empty state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pricing.tax-rates.index'))
        ->assertSuccessful()
        ->assertSee('No tax rates found for the selected filters.');
});

test('influencer can create tax rates', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TaxRatesForm::class)
        ->set('label', 'VAT')
        ->set('rate', '20.00')
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('pricing.tax-rates.index'));

    $this->assertDatabaseHas('tax_rates', [
        'user_id' => $user->id,
        'label' => 'VAT',
        'rate' => '20.00',
        'is_active' => 1,
    ]);
});

test('create form validates tax rate fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TaxRatesForm::class)
        ->set('label', '')
        ->set('rate', '101')
        ->call('save')
        ->assertHasErrors([
            'label',
            'rate',
        ]);
});

test('influencer can edit tax rates', function (): void {
    $user = User::factory()->create();
    $taxRate = TaxRate::factory()->for($user)->create([
        'label' => 'Old Label',
        'rate' => 6,
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(TaxRatesForm::class, ['taxRate' => $taxRate])
        ->set('label', 'Updated Label')
        ->set('rate', '7.50')
        ->set('is_active', false)
        ->call('save')
        ->assertRedirect(route('pricing.tax-rates.index'));

    $this->assertDatabaseHas('tax_rates', [
        'id' => $taxRate->id,
        'label' => 'Updated Label',
        'rate' => '7.50',
        'is_active' => 0,
    ]);
});

test('deleting tax rates works from list page', function (): void {
    $user = User::factory()->create();
    $taxRate = TaxRate::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(TaxRatesIndex::class)
        ->call('delete', $taxRate->id);

    $this->assertDatabaseMissing('tax_rates', [
        'id' => $taxRate->id,
    ]);
});

test('deleting tax rates works from edit form page', function (): void {
    $user = User::factory()->create();
    $taxRate = TaxRate::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(TaxRatesForm::class, ['taxRate' => $taxRate])
        ->call('delete')
        ->assertRedirect(route('pricing.tax-rates.index'));

    $this->assertDatabaseMissing('tax_rates', [
        'id' => $taxRate->id,
    ]);
});

test('users cannot access or mutate other user tax rates', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $taxRate = TaxRate::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('pricing.tax-rates.edit', $taxRate))
        ->assertForbidden();

    expect(function () use ($outsider, $taxRate): void {
        Livewire::actingAs($outsider)
            ->test(TaxRatesIndex::class)
            ->call('delete', $taxRate->id);
    })->toThrow(ModelNotFoundException::class);
});
