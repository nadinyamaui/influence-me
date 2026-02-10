<?php

namespace App\Services\Auth;

use App\Models\User;

final readonly class InstagramOAuthResult
{
    public function __construct(
        public User $user,
        public bool $shouldLogin,
    ) {}
}

