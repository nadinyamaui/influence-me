<?php

namespace App\Builders;

use App\Enums\MediaType;
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

    public function withInstagramAccount(): self
    {
        return $this->with('instagramAccount');
    }

    public function forUser(int $userId): self
    {
        return $this->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', $userId));
    }

    public function filterByMediaType(string $mediaType): self
    {
        if ($mediaType === 'all') {
            return $this;
        }

        if (! in_array($mediaType, MediaType::filters(), true)) {
            return $this;
        }

        return $this->where('media_type', $mediaType);
    }

    public function filterByAccount(string $accountId): self
    {
        if ($accountId === 'all') {
            return $this;
        }

        return $this->where('instagram_account_id', (int) $accountId);
    }

    public function withoutClientsForUser(int $userId): self
    {
        return $this->whereDoesntHave('campaigns', fn (Builder $builder): Builder => $builder
            ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId)));
    }

    public function forClientOwnedByUser(int $clientId, int $userId): self
    {
        return $this->whereHas('campaigns', fn (Builder $builder): Builder => $builder
            ->where('campaigns.client_id', $clientId)
            ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId)));
    }

    public function filterByClient(string $clientId, int $userId): self
    {
        if ($clientId === 'without_clients') {
            return $this->withoutClientsForUser($userId);
        }

        if ($clientId === 'all') {
            return $this;
        }

        return $this->forClientOwnedByUser((int) $clientId, $userId);
    }

    public function publishedFrom(?string $start): self
    {
        if (filled($start)) {
            return $this->whereDate('published_at', '>=', $start);
        }

        return $this;
    }

    public function publishedUntil(?string $end): self
    {
        if (filled($end)) {
            return $this->whereDate('published_at', '<=', $end);
        }

        return $this;
    }

    public function sortForGallery(string $field, string $direction): self
    {
        return $this->orderBy($field, $direction)
            ->orderByDesc('id');
    }

    public function withOwnedCampaignsForUser(int $userId): self
    {
        return $this->with([
            'campaigns' => fn ($builder) => $builder
                ->whereHas('client', fn (Builder $clientBuilder): Builder => $clientBuilder->where('user_id', $userId))
                ->with('client')
                ->orderBy('name'),
        ]);
    }
}
