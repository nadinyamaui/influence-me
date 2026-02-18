<?php

namespace App\Enums;

enum CatalogProductSort: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case NameAsc = 'name_asc';
    case NameDesc = 'name_desc';
    case PriceAsc = 'price_asc';
    case PriceDesc = 'price_desc';

    public static function default(): self
    {
        return self::Newest;
    }

    public static function values(): array
    {
        return array_map(
            static fn (CatalogProductSort $sort): string => $sort->value,
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
            self::PriceAsc->value => 'Price (Low to High)',
            self::PriceDesc->value => 'Price (High to Low)',
        ];
    }
}
