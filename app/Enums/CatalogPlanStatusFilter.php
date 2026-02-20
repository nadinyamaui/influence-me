<?php

namespace App\Enums;

enum CatalogPlanStatusFilter: string
{
    case Active = 'active';
    case Archived = 'archived';

    public static function default(): self
    {
        return self::Active;
    }

    public static function values(): array
    {
        return array_map(
            static fn (CatalogPlanStatusFilter $status): string => $status->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return [
            self::Active->value => 'Active',
            self::Archived->value => 'Archived',
        ];
    }

    public function activeValue(): ?bool
    {
        return match ($this) {
            self::Active => true,
            self::Archived => false,
        };
    }
}
