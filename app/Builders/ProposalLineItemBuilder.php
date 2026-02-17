<?php

namespace App\Builders;

use App\Models\ProposalLineItem;
use Illuminate\Database\Eloquent\Builder;

class ProposalLineItemBuilder extends Builder
{
    public function forProposal(int $proposalId): self
    {
        return $this->where('proposal_id', $proposalId);
    }

    public function ordered(): self
    {
        return $this->orderBy('sort_order')
            ->orderBy('id');
    }

    public function createForProposal(array $attributes, int $proposalId): ProposalLineItem
    {
        return $this->create(array_merge($attributes, [
            'proposal_id' => $proposalId,
        ]));
    }
}
