<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CampaignForm extends Form
{
    public string $name = '';

    public string $description = '';

    public ?string $proposalId = null;

    public function setCampaign(Campaign $campaign): void
    {
        $this->name = $campaign->name;
        $this->description = $campaign->description ?? '';
        $this->proposalId = $campaign->proposal_id !== null ? (string) $campaign->proposal_id : null;
    }

    public function clear(bool $clearProposal = true): void
    {
        $this->name = '';
        $this->description = '';

        if ($clearProposal) {
            $this->proposalId = null;
        }
    }

    public function validateForClient(int $clientId, int $userId, ?int $ignoreCampaignId = null, bool $includeProposal = true): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('campaigns', 'name')
                    ->where(fn ($builder) => $builder->where('client_id', $clientId))
                    ->ignore($ignoreCampaignId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ];

        if ($includeProposal) {
            $rules['proposalId'] = [
                'nullable',
                Rule::exists('proposals', 'id')->where(fn ($builder) => $builder
                    ->where('user_id', $userId)
                    ->where('client_id', $clientId)),
            ];
        }

        return $this->validate($rules);
    }

    public function payload(bool $includeProposal = true): array
    {
        $payload = [
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
        ];

        if ($includeProposal) {
            $payload['proposal_id'] = $this->proposalId !== null && $this->proposalId !== ''
                ? (int) $this->proposalId
                : null;
        }

        return $payload;
    }
}
