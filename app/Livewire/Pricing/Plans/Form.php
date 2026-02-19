<?php

namespace App\Livewire\Pricing\Plans;

use App\Http\Requests\StoreCatalogPlanRequest;
use App\Models\CatalogPlan;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogPlanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?int $planId = null;

    public string $name = '';

    public string $description = '';

    public string $currency = 'USD';

    public string $bundle_price = '';

    public bool $is_active = true;

    public array $items = [];

    public function mount($plan = null): void
    {
        if ($plan !== null) {
            $resolvedPlan = $plan instanceof CatalogPlan
                ? $plan
                : CatalogPlan::query()->findOrFail((int) $plan);

            $this->authorize('view', $resolvedPlan);
            $this->planId = $resolvedPlan->id;
            $this->fillFromPlan();

            return;
        }

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
        $validated = $this->validate();

        if ($this->planId !== null) {
            $plan = $this->resolvePlan();

            $this->authorize('update', $plan);
            $catalogPlanService->update(auth()->user(), $plan, $validated);

            session()->flash('status', 'Plan updated successfully.');

            return $this->redirectRoute('pricing.plans.index', navigate: true);
        }

        $this->authorize('create', CatalogPlan::class);
        $catalogPlanService->create(auth()->user(), $validated);

        session()->flash('status', 'Plan created successfully.');

        return $this->redirectRoute('pricing.plans.index', navigate: true);
    }

    public function render()
    {
        return view('pages.pricing.plans.form', [
            'products' => $this->availableProducts(),
            'isEditing' => $this->planId !== null,
        ])->layout('layouts.app', [
            'title' => $this->planId !== null ? __('Edit Pricing Plan') : __('Create Pricing Plan'),
        ]);
    }

    private function fillFromPlan(): void
    {
        $plan = $this->resolvePlan();
        $plan->loadMissing(['items']);

        $this->name = $plan->name;
        $this->description = $plan->description ?? '';
        $this->currency = $plan->currency;
        $this->bundle_price = $plan->bundle_price !== null ? (string) $plan->bundle_price : '';
        $this->is_active = $plan->is_active;

        $this->items = $plan->items
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

    private function resolvePlan(): CatalogPlan
    {
        return CatalogPlan::query()->findOrFail($this->planId);
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
