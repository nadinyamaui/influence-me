<?php

namespace App\Enums;

enum ClientType: string
{
    case Brand = 'brand';
    case Individual = 'individual';

    public static function values(): array
    {
        return array_map(
            static fn (ClientType $clientType): string => $clientType->value,
            self::cases(),
        );
    }

    public static function filters(): array
    {
        return array_merge(['all'], self::values());
    }
}
