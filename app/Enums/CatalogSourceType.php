<?php

namespace App\Enums;

enum CatalogSourceType: string
{
    case Product = 'product';
    case Plan = 'plan';
    case Custom = 'custom';

    public static function values(): array
    {
        return array_map(
            static fn (CatalogSourceType $sourceType): string => $sourceType->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Plan => 'Plan',
            self::Custom => 'Custom',
        };
    }
}
