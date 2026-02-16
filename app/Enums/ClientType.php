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

    public function label(): string
    {
        return match ($this) {
            self::Brand => 'Brand',
            self::Individual => 'Individual',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Brand => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200',
            self::Individual => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
        };
    }
}
