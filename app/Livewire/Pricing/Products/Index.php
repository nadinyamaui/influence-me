<?php

namespace App\Livewire\Pricing\Products;

use App\Enums\CatalogProductSort;
use App\Enums\CatalogProductStatusFilter;
use App\Enums\PlatformType;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogProductService;
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

    public string $platform = 'all';

    public string $sort = 'newest';

    public function mount(): void
    {
        $this->authorize('viewAny', CatalogProduct::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, CatalogProductStatusFilter::values(), true)) {
            $this->status = CatalogProductStatusFilter::default()->value;
        }

        $this->resetPage();
    }

    public function updatedPlatform(): void
    {
        if ($this->platform !== 'all' && ! in_array($this->platform, PlatformType::values(), true)) {
            $this->platform = 'all';
        }

        $this->resetPage();
    }

    public function updatedSort(): void
    {
        if (! in_array($this->sort, CatalogProductSort::values(), true)) {
            $this->sort = CatalogProductSort::default()->value;
        }

        $this->resetPage();
    }

    public function archive(CatalogProductService $catalogProductService, int $productId): void
    {
        $product = $this->resolveOwnedProduct($productId);
        $this->authorize('update', $product);

        $catalogProductService->archive(auth()->user(), $product);

        session()->flash('status', 'Product archived.');
    }

    public function unarchive(CatalogProductService $catalogProductService, int $productId): void
    {
        $product = $this->resolveOwnedProduct($productId);
        $this->authorize('update', $product);

        $catalogProductService->unarchive(auth()->user(), $product);

        session()->flash('status', 'Product unarchived.');
    }

    public function render()
    {
        return view('pages.pricing.products.index', [
            'products' => $this->products(),
            'statusOptions' => CatalogProductStatusFilter::options(),
            'platformOptions' => PlatformType::cases(),
            'sortOptions' => CatalogProductSort::options(),
        ])->layout('layouts.app', [
            'title' => __('Pricing Products'),
        ]);
    }

    private function products(): LengthAwarePaginator
    {
        $activeFilter = CatalogProductStatusFilter::from($this->status)->activeValue();
        $platformFilter = $this->platform === 'all' ? null : $this->platform;

        return CatalogProduct::query()
            ->forUser((int) auth()->id())
            ->search($this->search)
            ->filterByPlatform($platformFilter)
            ->filterByActive($activeFilter)
            ->applySort($this->sort)
            ->paginate(10);
    }

    private function resolveOwnedProduct(int $productId): CatalogProduct
    {
        return CatalogProduct::query()
            ->forUser((int) auth()->id())
            ->whereKey($productId)
            ->firstOrFail();
    }
}
