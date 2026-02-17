<?php

namespace App\Builders;

use App\Enums\CatalogSourceType;
use App\Models\CatalogPlan;
use App\Models\CatalogProduct;
use App\Models\Proposal;
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

    public function createForProposal(array $attributes, int $proposalId, ?int $userId = null): ProposalLineItem
    {
        $userId ??= auth()->id();

        Proposal::query()
            ->whereKey($proposalId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $sourceType = $attributes['source_type'] ?? null;
        $sourceType = $sourceType instanceof CatalogSourceType
            ? $sourceType
            : CatalogSourceType::tryFrom((string) $sourceType);
        $sourceId = $attributes['source_id'] ?? null;

        if ($sourceId !== null && $sourceType === CatalogSourceType::Product) {
            CatalogProduct::query()
                ->whereKey($sourceId)
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        if ($sourceId !== null && $sourceType === CatalogSourceType::Plan) {
            CatalogPlan::query()
                ->whereKey($sourceId)
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        return $this->create(array_merge($attributes, [
            'proposal_id' => $proposalId,
        ]));
    }
}
