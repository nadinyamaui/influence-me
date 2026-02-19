<?php

namespace App\Livewire\Pricing\Plans;

use App\Http\Requests\StoreCatalogPlanRequest;
use App\Models\CatalogPlan;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogPlanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public CatalogPlan $plan;

    public string $name = '';

    public string $description = '';

    public string $currency = 'USD';

    public string $bundle_price = '';

    public bool $is_active = true;

    public array $items = [];

    public function mount(CatalogPlan $plan): void
    {
        $this->authorize('view', $plan);

        $this->plan = $plan;
        $this->fillFromPlan();
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
        $this->authorize('update', $this->plan);

        $validated = $this->validate();
        $catalogPlanService->update(auth()->user(), $this->plan, $validated);

        session()->flash('status', 'Plan updated successfully.');

        return $this->redirectRoute('pricing.plans.index', navigate: true);
    }

    public function render()
    {
        return view('pages.pricing.plans.edit', [
            'products' => $this->availableProducts(),
        ])->layout('layouts.app', [
            'title' => __('Edit Pricing Plan'),
        ]);
    }

    private function fillFromPlan(): void
    {
        $this->plan->loadMissing(['items']);

        $this->name = $this->plan->name;
        $this->description = $this->plan->description ?? '';
        $this->currency = $this->plan->currency;
        $this->bundle_price = $this->plan->bundle_price !== null ? (string) $this->plan->bundle_price : '';
        $this->is_active = $this->plan->is_active;

        $this->items = $this->plan->items
            ->map(fn ($item): array => [
                'catalog_product_id' => (string) $item->catalog_product_id,
                'quantity' => (string) $item->quantity,
                'unit_price_override' => $item->unit_price_override !== null ? (string) $item->unit_price_override : '',
            ])
            ->values()
            ->all();

        if ($this->items === []) {
            $this->items = [$this->emptyItem()];
        }
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
