<?php

namespace App\Enums;

enum CatalogPlanSort: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case NameAsc = 'name_asc';
    case NameDesc = 'name_desc';
    case BundlePriceAsc = 'bundle_price_asc';
    case BundlePriceDesc = 'bundle_price_desc';
    case ItemsDesc = 'items_desc';
    case ItemsAsc = 'items_asc';

    public static function default(): self
    {
        return self::Newest;
    }

    public static function values(): array
    {
        return array_map(
            static fn (CatalogPlanSort $sort): string => $sort->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return [
            self::Newest->value => 'Newest',
            self::Oldest->value => 'Oldest',
            self::NameAsc->value => 'Name (A-Z)',
            self::NameDesc->value => 'Name (Z-A)',
            self::BundlePriceAsc->value => 'Bundle Price (Low to High)',
            self::BundlePriceDesc->value => 'Bundle Price (High to Low)',
            self::ItemsDesc->value => 'Most Items',
            self::ItemsAsc->value => 'Fewest Items',
        ];
    }
}
