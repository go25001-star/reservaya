<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleAndMiddleware
{
     public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth('api')->user();

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        foreach ($roles as $role) {
            if (!$user->hasRole($role)) {
                throw UnauthorizedException::forRoles($roles);
            }
        }

        return $next($request);
    }
}
