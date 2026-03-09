<?php

namespace App\Services\AthenasCodex;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CatalogService
{
    private ?array $catalog = null;

    public function all(): array
    {
        if ($this->catalog !== null) {
            return $this->catalog;
        }

        $this->catalog = collect(config('athenas_codex.products', []))
            ->map(fn (array $product): array => $this->normalizeProduct($product))
            ->values()
            ->all();

        session()->put('athenas_codex.catalog.products', $this->catalog);

        return $this->catalog;
    }

    public function products(array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $products = collect($this->all())
            ->when(
                $normalizedFilters['category'] !== 'all',
                fn (Collection $collection): Collection => $collection->where('category', $normalizedFilters['category'])
            )
            ->when(
                $normalizedFilters['availability'] === 'in-stock',
                fn (Collection $collection): Collection => $collection->where('available', true)
            )
            ->when(
                $normalizedFilters['search'] !== '',
                fn (Collection $collection): Collection => $collection->filter(
                    fn (array $product): bool => Str::contains(
                        Str::lower("{$product['name']} {$product['code']} {$product['category_label']}"),
                        $normalizedFilters['search']
                    )
                )
            )
            ->values()
            ->all();

        session()->put('athenas_codex.catalog.filtered_products', $products);

        return $products;
    }

    public function featured(int $limit = 6): array
    {
        return collect($this->all())
            ->where('featured', true)
            ->take($limit)
            ->values()
            ->all();
    }

    public function categories(): array
    {
        $products = collect($this->all());
        $categories = collect(config('athenas_codex.category_labels', []))
            ->map(fn (string $label, string $key): array => [
                'value' => $key,
                'label' => $label,
                'count' => $products->where('category', $key)->count(),
            ])
            ->values()
            ->all();

        array_unshift($categories, [
            'value' => 'all',
            'label' => 'Todo el catalogo',
            'count' => $products->count(),
        ]);

        return $categories;
    }

    public function stats(): array
    {
        $products = collect($this->all());
        $availableProducts = $products->where('available', true);

        return [
            'product_count' => $products->count(),
            'available_count' => $availableProducts->count(),
            'sold_out_count' => $products->where('available', false)->count(),
            'category_count' => count(config('athenas_codex.category_labels', [])),
            'starting_price' => $availableProducts->min('price') ?? 0,
            'formatted_starting_price' => $this->formatMoney((float) ($availableProducts->min('price') ?? 0)),
        ];
    }

    public function findOrFail(string $sku): array
    {
        $product = collect($this->all())
            ->firstWhere('sku', $sku);

        if ($product === null) {
            throw new InvalidArgumentException("The product [{$sku}] is not part of the Athenas catalog.");
        }

        return $product;
    }

    public function productSkus(): array
    {
        return collect($this->all())
            ->pluck('sku')
            ->all();
    }

    public function formatMoney(float $amount): string
    {
        return config('athenas_codex.currency.prefix').number_format($amount, 2, ',', '.');
    }

    public function normalizeFilters(array $filters): array
    {
        return [
            'search' => Str::of((string) ($filters['search'] ?? ''))->trim()->lower()->value(),
            'category' => (string) ($filters['category'] ?? 'all'),
            'availability' => (string) ($filters['availability'] ?? 'all'),
        ];
    }

    private function normalizeProduct(array $product): array
    {
        $price = round((float) $product['price'], 2);
        $category = (string) $product['category'];
        $available = (bool) ($product['available'] ?? true);

        return [
            ...$product,
            'category_label' => (string) (config("athenas_codex.category_labels.{$category}") ?? Str::headline($category)),
            'price' => $price,
            'formatted_price' => $this->formatMoney($price),
            'available' => $available,
            'availability_label' => $available ? 'Disponible' : 'Agotado',
            'materials' => config('athenas_codex.materials', []),
            'colors' => collect($product['colors'] ?? [])
                ->map(fn (string $color): array => [
                    'key' => $color,
                    'label' => (string) (config("athenas_codex.color_swatches.{$color}.label") ?? Str::headline($color)),
                    'hex' => (string) (config("athenas_codex.color_swatches.{$color}.hex") ?? '#e5e7eb'),
                ])
                ->values()
                ->all(),
            'featured' => (bool) ($product['featured'] ?? false),
        ];
    }
}
