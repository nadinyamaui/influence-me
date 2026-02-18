<?php

namespace App\Livewire\Pricing\Products;

use App\Enums\BillingUnitType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use App\Http\Requests\StoreCatalogProductRequest;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public CatalogProduct $product;

    public string $name = '';

    public string $platform = 'instagram';

    public string $media_type = '';

    public string $billing_unit = 'deliverable';

    public string $base_price = '';

    public string $currency = 'USD';

    public bool $is_active = true;

    public function mount(CatalogProduct $product): void
    {
        $this->authorize('view', $product);

        $this->product = $product;
        $this->fillFromProduct();
    }

    protected function rules(): array
    {
        return StoreCatalogProductRequest::initialRules();
    }

    public function save(CatalogProductService $catalogProductService)
    {
        $this->authorize('update', $this->product);

        $validated = $this->validate();

        $catalogProductService->update(auth()->user(), $this->product, $validated);

        session()->flash('status', 'Product updated successfully.');

        return $this->redirectRoute('pricing.products.index', navigate: true);
    }

    public function archive(CatalogProductService $catalogProductService)
    {
        $this->authorize('update', $this->product);

        $catalogProductService->archive(auth()->user(), $this->product);

        $this->is_active = false;
        session()->flash('status', 'Product archived.');
    }

    public function unarchive(CatalogProductService $catalogProductService)
    {
        $this->authorize('update', $this->product);

        $catalogProductService->unarchive(auth()->user(), $this->product);

        $this->is_active = true;
        session()->flash('status', 'Product unarchived.');
    }

    public function render()
    {
        return view('pages.pricing.products.edit', [
            'platforms' => PlatformType::cases(),
            'mediaTypes' => MediaType::cases(),
            'billingUnits' => BillingUnitType::cases(),
        ])->layout('layouts.app', [
            'title' => __('Edit Pricing Product'),
        ]);
    }

    private function fillFromProduct(): void
    {
        $this->name = $this->product->name;
        $this->platform = $this->product->platform->value;
        $this->media_type = $this->product->media_type?->value ?? '';
        $this->billing_unit = $this->product->billing_unit->value;
        $this->base_price = (string) $this->product->base_price;
        $this->currency = $this->product->currency;
        $this->is_active = $this->product->is_active;
    }
}
