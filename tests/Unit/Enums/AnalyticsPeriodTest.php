<?php

use App\Enums\AnalyticsPeriod;
use Carbon\CarbonImmutable;

it('provides bounded startsAt values for fixed reference times', function (AnalyticsPeriod $period, int $days): void {
    $reference = CarbonImmutable::parse('2026-02-20 15:30:00');

    expect($period->startsAt($reference))->toEqual($reference->subDays($days));
})->with([
    [AnalyticsPeriod::SevenDays, 7],
    [AnalyticsPeriod::ThirtyDays, 30],
    [AnalyticsPeriod::NinetyDays, 90],
]);

it('returns null startsAt for all time', function (): void {
    $reference = CarbonImmutable::parse('2026-02-20 15:30:00');

    expect(AnalyticsPeriod::AllTime->startsAt($reference))->toBeNull();
});

it('provides bounded previous window start values for fixed reference times', function (AnalyticsPeriod $period, int $days): void {
    $reference = CarbonImmutable::parse('2026-02-20 15:30:00');

    expect($period->previousWindowStart($reference))->toEqual($reference->subDays($days));
})->with([
    [AnalyticsPeriod::SevenDays, 14],
    [AnalyticsPeriod::ThirtyDays, 60],
    [AnalyticsPeriod::NinetyDays, 180],
]);

it('returns null previous window start for all time', function (): void {
    $reference = CarbonImmutable::parse('2026-02-20 15:30:00');

    expect(AnalyticsPeriod::AllTime->previousWindowStart($reference))->toBeNull();
});

it('uses current time when no reference is provided', function (): void {
    $reference = CarbonImmutable::parse('2026-02-20 15:30:00');

    CarbonImmutable::setTestNow($reference);

    try {
        expect(AnalyticsPeriod::SevenDays->startsAt())->toEqual($reference->subDays(7))
            ->and(AnalyticsPeriod::ThirtyDays->previousWindowStart())->toEqual($reference->subDays(60));
    } finally {
        CarbonImmutable::setTestNow();
    }
});
