<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampaignBuilder extends Builder
{
    public function orderedByName(): self
    {
        return $this->orderBy('name');
    }

    public function withProposalAndMediaCount(): self
    {
        return $this->with('proposal')->withCount('instagramMedia');
    }

    public function withInstagramMediaOrderedByPublishedAtDesc(): self
    {
        return $this->with([
            'instagramMedia' => fn ($builder) => $builder->orderByDesc('published_at'),
        ]);
    }
}
