<?php

namespace App\Livewire\Pricing\Products;

use App\Enums\CatalogProductSort;
use App\Enums\CatalogProductStatusFilter;
use App\Enums\PlatformType;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'active')]
    public string $status = 'active';

    #[Url(except: 'all')]
    public string $platform = 'all';

    #[Url(except: 'newest')]
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

    public function sortBy(string $column): void
    {
        [$ascending, $descending] = $this->sortPair($column);
        if ($ascending === null || $descending === null) {
            return;
        }

        if ($this->sort === $ascending) {
            $this->sort = $descending;
        } elseif ($this->sort === $descending) {
            $this->sort = $ascending;
        } else {
            $this->sort = $column === 'created_at' ? $descending : $ascending;
        }

        $this->resetPage();
    }

    public function isSortedBy(string $column): bool
    {
        [$ascending, $descending] = $this->sortPair($column);
        if ($ascending === null || $descending === null) {
            return false;
        }

        return in_array($this->sort, [$ascending, $descending], true);
    }

    public function sortDirection(string $column): ?string
    {
        [$ascending, $descending] = $this->sortPair($column);
        if ($ascending === null || $descending === null) {
            return null;
        }

        return match ($this->sort) {
            $ascending => 'asc',
            $descending => 'desc',
            default => null,
        };
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

    private function sortPair(string $column): array
    {
        return match ($column) {
            'name' => [CatalogProductSort::NameAsc->value, CatalogProductSort::NameDesc->value],
            'price' => [CatalogProductSort::PriceAsc->value, CatalogProductSort::PriceDesc->value],
            'created_at' => [CatalogProductSort::Oldest->value, CatalogProductSort::Newest->value],
            default => [null, null],
        };
    }
}
