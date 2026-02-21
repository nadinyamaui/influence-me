<?php

namespace App\Enums;

enum TaxRateStatusFilter: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public static function default(): self
    {
        return self::Active;
    }

    public static function values(): array
    {
        return array_map(
            static fn (TaxRateStatusFilter $status): string => $status->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return [
            self::Active->value => 'Active',
            self::Inactive->value => 'Inactive',
        ];
    }

    public function activeValue(): ?bool
    {
        return match ($this) {
            self::Active => true,
            self::Inactive => false,
        };
    }
}
