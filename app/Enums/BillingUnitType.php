<?php

namespace App\Enums;

enum BillingUnitType: string
{
    case Deliverable = 'deliverable';
    case Package = 'package';

    public static function values(): array
    {
        return array_map(
            static fn (BillingUnitType $billingUnit): string => $billingUnit->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Deliverable => 'Deliverable',
            self::Package => 'Package',
        };
    }
}
