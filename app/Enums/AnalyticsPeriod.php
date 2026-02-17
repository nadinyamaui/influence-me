<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

enum AnalyticsPeriod: string
{
    case SevenDays = '7_days';
    case ThirtyDays = '30_days';
    case NinetyDays = '90_days';
    case AllTime = 'all';

    public static function default(): self
    {
        return self::ThirtyDays;
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $period): string => $period->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(static fn (self $period): array => [$period->value => $period->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::SevenDays => '7 Days',
            self::ThirtyDays => '30 Days',
            self::NinetyDays => '90 Days',
            self::AllTime => 'All Time',
        };
    }

    public function startsAt(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $reference = $now ?? CarbonImmutable::now();

        return match ($this) {
            self::SevenDays => $reference->subDays(7),
            self::ThirtyDays => $reference->subDays(30),
            self::NinetyDays => $reference->subDays(90),
            self::AllTime => null,
        };
    }

    public function previousWindowStart(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $reference = $now ?? CarbonImmutable::now();

        return match ($this) {
            self::SevenDays => $reference->subDays(14),
            self::ThirtyDays => $reference->subDays(60),
            self::NinetyDays => $reference->subDays(180),
            self::AllTime => null,
        };
    }
}
