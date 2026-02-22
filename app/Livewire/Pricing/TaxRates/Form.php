<?php

namespace App\Livewire\Pricing\TaxRates;

use App\Http\Requests\StoreTaxRateRequest;
use App\Models\TaxRate;
use App\Services\Catalog\TaxRateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $taxRate = null;

    public string $label = '';

    public string $rate = '';

    public bool $is_active = true;

    public function mount($taxRate = null): void
    {
        if ($taxRate === null) {
            $this->authorize('create', TaxRate::class);

            return;
        }

        if (! $taxRate instanceof TaxRate) {
            $taxRate = TaxRate::query()
                ->whereKey($taxRate)
                ->firstOrFail();
        }

        $this->authorize('view', $taxRate);

        $this->taxRate = $taxRate;
        $this->fillFromTaxRate();
    }

    protected function rules(): array
    {
        return StoreTaxRateRequest::initialRules();
    }

    public function save(TaxRateService $taxRateService)
    {
        $validated = $this->validate();

        if ($this->isEditMode()) {
            $taxRate = $this->taxRateForUpdate();

            $this->authorize('update', $taxRate);
            $taxRateService->update(auth()->user(), $taxRate, $validated);

            session()->flash('status', 'Tax rate updated successfully.');

            return $this->redirectRoute('pricing.tax-rates.index', navigate: true);
        }

        $this->authorize('create', TaxRate::class);
        $taxRateService->create(auth()->user(), $validated);

        session()->flash('status', 'Tax rate created successfully.');

        return $this->redirectRoute('pricing.tax-rates.index', navigate: true);
    }

    public function delete(TaxRateService $taxRateService)
    {
        $taxRate = $this->taxRateForUpdate();

        $this->authorize('delete', $taxRate);
        $taxRateService->delete(auth()->user(), $taxRate);

        session()->flash('status', 'Tax rate deleted.');

        return $this->redirectRoute('pricing.tax-rates.index', navigate: true);
    }

    public function render()
    {
        $isEditMode = $this->isEditMode();

        return view('pages.pricing.tax-rates.form', [
            'isEditMode' => $isEditMode,
        ])->layout('layouts.app', [
            'title' => $isEditMode
                ? __('Edit Tax Rate')
                : __('Create Tax Rate'),
        ]);
    }

    private function fillFromTaxRate(): void
    {
        $taxRate = $this->taxRateForUpdate();

        $this->label = $taxRate->label;
        $this->rate = (string) $taxRate->rate;
        $this->is_active = $taxRate->is_active;
    }

    private function isEditMode(): bool
    {
        return $this->taxRate instanceof TaxRate;
    }

    private function taxRateForUpdate(): TaxRate
    {
        abort_unless($this->taxRate instanceof TaxRate, 404);

        return $this->taxRate;
    }
}
