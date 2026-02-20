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

class Form extends Component
{
    use AuthorizesRequests;

    public $product = null;

    public string $name = '';

    public string $platform = 'instagram';

    public string $media_type = '';

    public string $billing_unit = 'deliverable';

    public string $base_price = '';

    public string $currency = 'USD';

    public bool $is_active = true;

    public function mount($product = null): void
    {
        if ($product === null) {
            $this->authorize('create', CatalogProduct::class);

            return;
        }

        if (! $product instanceof CatalogProduct) {
            $product = CatalogProduct::query()
                ->whereKey($product)
                ->firstOrFail();
        }

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
        $validated = $this->validate();

        if ($this->isEditMode()) {
            $product = $this->productForUpdate();

            $this->authorize('update', $product);
            $catalogProductService->update(auth()->user(), $product, $validated);

            session()->flash('status', 'Product updated successfully.');

            return $this->redirectRoute('pricing.products.index', navigate: true);
        }

        $this->authorize('create', CatalogProduct::class);
        $catalogProductService->create(auth()->user(), $validated);

        session()->flash('status', 'Product created successfully.');

        return $this->redirectRoute('pricing.products.index', navigate: true);
    }

    public function archive(CatalogProductService $catalogProductService): void
    {
        $product = $this->productForUpdate();

        $this->authorize('update', $product);

        $catalogProductService->archive(auth()->user(), $product);

        $this->is_active = false;
        session()->flash('status', 'Product archived.');
    }

    public function unarchive(CatalogProductService $catalogProductService): void
    {
        $product = $this->productForUpdate();

        $this->authorize('update', $product);

        $catalogProductService->unarchive(auth()->user(), $product);

        $this->is_active = true;
        session()->flash('status', 'Product unarchived.');
    }

    public function render()
    {
        $isEditMode = $this->isEditMode();

        return view('pages.pricing.products.form', [
            'platforms' => PlatformType::cases(),
            'mediaTypes' => MediaType::cases(),
            'billingUnits' => BillingUnitType::cases(),
            'isEditMode' => $isEditMode,
        ])->layout('layouts.app', [
            'title' => $isEditMode
                ? __('Edit Pricing Product')
                : __('Create Pricing Product'),
        ]);
    }

    private function fillFromProduct(): void
    {
        $product = $this->productForUpdate();

        $this->name = $product->name;
        $this->platform = $product->platform->value;
        $this->media_type = $product->media_type?->value ?? '';
        $this->billing_unit = $product->billing_unit->value;
        $this->base_price = (string) $product->base_price;
        $this->currency = $product->currency;
        $this->is_active = $product->is_active;
    }

    private function isEditMode(): bool
    {
        return $this->product instanceof CatalogProduct;
    }

    private function productForUpdate(): CatalogProduct
    {
        abort_unless($this->product instanceof CatalogProduct, 404);

        return $this->product;
    }
}
