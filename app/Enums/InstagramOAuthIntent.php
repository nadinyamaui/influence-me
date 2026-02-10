<?php

namespace App\Enums;

enum InstagramOAuthIntent: string
{
    case Login = 'login';
    case AddAccount = 'add_account';

    public function failureRoute(): string
    {
        return $this === self::AddAccount ? 'dashboard' : 'login';
    }
}
