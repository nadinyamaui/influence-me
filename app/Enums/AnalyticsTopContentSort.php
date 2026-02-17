<?php

namespace App\Enums;

enum AnalyticsTopContentSort: string
{
    case Engagement = 'engagement';
    case Reach = 'reach';

    public static function default(): self
    {
        return self::Engagement;
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $sort): string => $sort->value,
            self::cases(),
        );
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(static fn (self $sort): array => [$sort->value => $sort->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Engagement => 'Top by Engagement',
            self::Reach => 'Top by Reach',
        };
    }

    public function metricColumn(): string
    {
        return match ($this) {
            self::Engagement => 'engagement_rate',
            self::Reach => 'reach',
        };
    }
}
