<?php

namespace App\Livewire\Pricing\TaxRates;

use App\Enums\TaxRateSort;
use App\Enums\TaxRateStatusFilter;
use App\Models\TaxRate;
use App\Services\Catalog\TaxRateService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $status = 'active';

    public string $sort = 'newest';

    public function mount(): void
    {
        $this->authorize('viewAny', TaxRate::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, TaxRateStatusFilter::values(), true)) {
            $this->status = TaxRateStatusFilter::default()->value;
        }

        $this->resetPage();
    }

    public function updatedSort(): void
    {
        if (! in_array($this->sort, TaxRateSort::values(), true)) {
            $this->sort = TaxRateSort::default()->value;
        }

        $this->resetPage();
    }

    public function delete(TaxRateService $taxRateService, int $taxRateId): void
    {
        $taxRate = $this->resolveOwnedTaxRate($taxRateId);
        $this->authorize('delete', $taxRate);

        $taxRateService->delete(auth()->user(), $taxRate);

        session()->flash('status', 'Tax rate deleted.');
    }

    public function render()
    {
        return view('pages.pricing.tax-rates.index', [
            'taxRates' => $this->taxRates(),
            'statusOptions' => TaxRateStatusFilter::options(),
            'sortOptions' => TaxRateSort::options(),
        ])->layout('layouts.app', [
            'title' => __('Tax Rates'),
        ]);
    }

    private function taxRates(): LengthAwarePaginator
    {
        $status = in_array($this->status, TaxRateStatusFilter::values(), true)
            ? $this->status
            : TaxRateStatusFilter::default()->value;

        $sort = in_array($this->sort, TaxRateSort::values(), true)
            ? $this->sort
            : TaxRateSort::default()->value;

        return TaxRate::query()
            ->forUser((int) auth()->id())
            ->search($this->search)
            ->filterByActive(TaxRateStatusFilter::from($status)->activeValue())
            ->applySort($sort)
            ->paginate(10);
    }

    private function resolveOwnedTaxRate(int $taxRateId): TaxRate
    {
        return TaxRate::query()
            ->forUser((int) auth()->id())
            ->whereKey($taxRateId)
            ->firstOrFail();
    }
}
