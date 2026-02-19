<?php

namespace App\Livewire\Pricing\Plans;

use App\Http\Requests\StoreCatalogPlanRequest;
use App\Models\CatalogPlan;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogPlanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $description = '';

    public string $currency = 'USD';

    public string $bundle_price = '';

    public bool $is_active = true;

    public array $items = [];

    public function mount(): void
    {
        $this->authorize('create', CatalogPlan::class);
        $this->items = [$this->emptyItem()];
    }

    protected function rules(): array
    {
        return StoreCatalogPlanRequest::initialRules((int) auth()->id());
    }

    public function addItemRow(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItemRow(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(CatalogPlanService $catalogPlanService)
    {
        $this->authorize('create', CatalogPlan::class);

        $validated = $this->validate();
        $catalogPlanService->create(auth()->user(), $validated);

        session()->flash('status', 'Plan created successfully.');

        return $this->redirectRoute('pricing.plans.index', navigate: true);
    }

    public function render()
    {
        return view('pages.pricing.plans.create', [
            'products' => $this->availableProducts(),
        ])->layout('layouts.app', [
            'title' => __('Create Pricing Plan'),
        ]);
    }

    private function availableProducts(): Collection
    {
        return CatalogProduct::query()
            ->forUser((int) auth()->id())
            ->activeOnly()
            ->applySort('name_asc')
            ->get(['id', 'name', 'base_price', 'currency']);
    }

    private function emptyItem(): array
    {
        return [
            'catalog_product_id' => '',
            'quantity' => '1',
            'unit_price_override' => '',
        ];
    }
}
