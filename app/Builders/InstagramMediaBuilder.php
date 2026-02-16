<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class InstagramMediaBuilder extends Builder
{
    public function forClient(int $clientId): self
    {
        return $this->whereHas('campaigns', fn (Builder $builder): Builder => $builder->where('campaigns.client_id', $clientId));
    }

    public function latestPublished(): self
    {
        return $this->orderByDesc('published_at');
    }

    public function distinctMediaRows(): self
    {
        return $this->select('instagram_media.*')->distinct();
    }
}
