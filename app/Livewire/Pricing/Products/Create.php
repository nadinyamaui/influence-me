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

class Create extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $platform = 'instagram';

    public string $media_type = '';

    public string $billing_unit = 'deliverable';

    public string $base_price = '';

    public string $currency = 'USD';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('create', CatalogProduct::class);
    }

    protected function rules(): array
    {
        return StoreCatalogProductRequest::initialRules();
    }

    public function save(CatalogProductService $catalogProductService)
    {
        $this->authorize('create', CatalogProduct::class);

        $validated = $this->validate();
        $catalogProductService->create(auth()->user(), $validated);

        session()->flash('status', 'Product created successfully.');

        return $this->redirectRoute('pricing.products.index', navigate: true);
    }

    public function render()
    {
        return view('pages.pricing.products.create', [
            'platforms' => PlatformType::cases(),
            'mediaTypes' => MediaType::cases(),
            'billingUnits' => BillingUnitType::cases(),
        ])->layout('layouts.app', [
            'title' => __('Create Pricing Product'),
        ]);
    }
}
