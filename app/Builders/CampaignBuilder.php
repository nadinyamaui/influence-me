<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampaignBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->whereHas('client', fn (Builder $builder): Builder => $builder->where('user_id', $userId));
    }

    public function forClient(int $clientId): self
    {
        return $this->where('client_id', $clientId);
    }

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

    public function withInstagramMediaAnalyticsMetrics(): self
    {
        return $this->with([
            'instagramMedia' => fn ($builder) => $builder->select([
                'instagram_media.id',
                'instagram_media.reach',
                'instagram_media.engagement_rate',
            ]),
        ]);
    }
}
