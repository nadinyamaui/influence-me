<?php

namespace App\Enums;

enum TaxRateSort: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case LabelAsc = 'label_asc';
    case LabelDesc = 'label_desc';
    case RateAsc = 'rate_asc';
    case RateDesc = 'rate_desc';

    public static function default(): self
    {
        return self::Newest;
    }

    public static function values(): array
    {
        return array_map(
            static fn (TaxRateSort $sort): string => $sort->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return [
            self::Newest->value => 'Newest',
            self::Oldest->value => 'Oldest',
            self::LabelAsc->value => 'Label (A-Z)',
            self::LabelDesc->value => 'Label (Z-A)',
            self::RateAsc->value => 'Rate (Low to High)',
            self::RateDesc->value => 'Rate (High to Low)',
        ];
    }
}
