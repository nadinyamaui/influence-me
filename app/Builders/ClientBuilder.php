<?php

namespace App\Builders;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Builder;

class ClientBuilder extends Builder
{
    public function search(string $term): self
    {
        $search = trim($term);
        if ($search === '') {
            return $this;
        }

        $searchTerm = '%'.$search.'%';

        return $this->where(function (Builder $builder) use ($searchTerm): void {
            $builder->where('name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhere('company_name', 'like', $searchTerm);
        });
    }

    public function filterByType(string $type): self
    {
        if (! in_array($type, ClientType::values(), true)) {
            return $this;
        }

        return $this->where('type', $type);
    }
}
