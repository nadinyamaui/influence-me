<?php

namespace App\Services\Clients;

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Illuminate\Support\Collection;

class ClientAudienceDemographicsService
{
    public function build(Client $client): array
    {
        $accountIds = InstagramMedia::query()
            ->forClient($client->id)
            ->distinctMediaRows()
            ->pluck('instagram_account_id')
            ->map(fn ($accountId): int => (int) $accountId)
            ->unique()
            ->values();

        if ($accountIds->isEmpty()) {
            return $this->empty();
        }

        $demographics = AudienceDemographic::query()
            ->whereIn('instagram_account_id', $accountIds->all())
            ->get(['instagram_account_id', 'type', 'dimension', 'value'])
            ->groupBy(fn (AudienceDemographic $item): string => $item->type->value);

        $accountWeights = InstagramAccount::query()
            ->whereIn('id', $accountIds->all())
            ->pluck('followers_count', 'id')
            ->mapWithKeys(fn ($followersCount, $id): array => [(int) $id => max((int) $followersCount, 1)]);

        $ageOrder = collect(['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+']);

        $ageRows = $demographics->get(DemographicType::Age->value, collect());
        $ageLookup = $this->weightedDemographicValues($ageRows, $accountWeights);

        $genderRows = $demographics->get(DemographicType::Gender->value, collect());
        $genderKeys = collect(['Male', 'Female', 'Other']);
        $genderLookup = $this->weightedDemographicValues($genderRows, $accountWeights);
        $genderValues = $genderKeys
            ->map(fn (string $label): float => (float) ($genderLookup->get($label) ?? 0.0))
            ->all();

        $cityRows = $demographics->get(DemographicType::City->value, collect());
        $countryRows = $demographics->get(DemographicType::Country->value, collect());

        return [
            'has_data' => $ageRows->isNotEmpty() || $genderRows->isNotEmpty() || $cityRows->isNotEmpty() || $countryRows->isNotEmpty(),
            'age' => [
                'labels' => $ageOrder->all(),
                'values' => $ageOrder
                    ->map(fn (string $label): float => (float) ($ageLookup->get($label) ?? 0.0))
                    ->all(),
            ],
            'gender' => [
                'labels' => $genderKeys->all(),
                'values' => $genderValues,
                'colors' => ['#3b82f6', '#ec4899', '#9ca3af'],
                'total' => round(array_sum($genderValues), 2),
            ],
            'city' => $this->topDemographicRows($cityRows, $accountWeights),
            'country' => $this->topDemographicRows($countryRows, $accountWeights),
        ];
    }

    public function empty(): array
    {
        return [
            'has_data' => false,
            'age' => [
                'labels' => ['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'],
                'values' => [0, 0, 0, 0, 0, 0, 0],
            ],
            'gender' => [
                'labels' => ['Male', 'Female', 'Other'],
                'values' => [0, 0, 0],
                'colors' => ['#3b82f6', '#ec4899', '#9ca3af'],
                'total' => 0.0,
            ],
            'city' => [
                'labels' => [],
                'values' => [],
            ],
            'country' => [
                'labels' => [],
                'values' => [],
            ],
        ];
    }

    private function topDemographicRows(Collection $rows, Collection $accountWeights): array
    {
        $grouped = $this->weightedDemographicValues($rows, $accountWeights)
            ->sortDesc()
            ->take(10);

        return [
            'labels' => $grouped->keys()->values()->all(),
            'values' => $grouped->values()->all(),
        ];
    }

    private function weightedDemographicValues(Collection $rows, Collection $accountWeights): Collection
    {
        $typeAccountIds = $rows->pluck('instagram_account_id')
            ->map(fn ($accountId): int => (int) $accountId)
            ->unique()
            ->values()
            ->all();

        $scopedWeights = $accountWeights->only($typeAccountIds);

        if ($scopedWeights->isEmpty()) {
            $scopedWeights = $accountWeights;
        }

        return $rows
            ->groupBy('dimension')
            ->map(function (Collection $dimensionRows) use ($scopedWeights): float {
                $perAccount = $dimensionRows
                    ->groupBy('instagram_account_id')
                    ->map(fn (Collection $accountRows): float => (float) $accountRows->sum(fn (AudienceDemographic $item): float => (float) $item->value));

                $weightedSum = 0.0;
                $totalWeight = 0;

                foreach ($scopedWeights as $accountId => $weight) {
                    $value = (float) ($perAccount->get((int) $accountId) ?? 0.0);
                    $weightedSum += $value * (int) $weight;
                    $totalWeight += $weight;
                }

                return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
            });
    }
}
