<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Closure;
use Illuminate\Http\Request;

class AuthenticateClient extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        return parent::handle($request, $next, 'client');
    }

    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return route('portal.login');
    }
}
