<?php

namespace App\Services\Catalog;

use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TaxRateService
{
    public function create(User $user, array $payload): TaxRate
    {
        return TaxRate::query()->createForUser($this->normalizePayload($payload), $user->id);
    }

    public function update(User $user, TaxRate $taxRate, array $payload): TaxRate
    {
        $this->ensureOwner($user, $taxRate);

        $taxRate->update($this->normalizePayload($payload));

        return $taxRate->refresh();
    }

    public function delete(User $user, TaxRate $taxRate): void
    {
        $this->ensureOwner($user, $taxRate);

        $taxRate->delete();
    }

    private function ensureOwner(User $user, TaxRate $taxRate): void
    {
        if ($taxRate->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to manage this tax rate.');
        }
    }

    private function normalizePayload(array $payload): array
    {
        return [
            'label' => $payload['label'],
            'rate' => $payload['rate'],
            'is_active' => (bool) $payload['is_active'],
        ];
    }
}
