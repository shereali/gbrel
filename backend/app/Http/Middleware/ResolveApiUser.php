<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the signed-in user from the bearer token so routes can call $request->user().
 * It never blocks a request; RequireApiUser and RequireStaff do the blocking.
 */
class ResolveApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $userId = $token ? Cache::get('gbrel_auth_token_'.$token) : null;
        $user = $userId ? User::find($userId) : null;

        if ($user && strtolower((string) $user->status) === 'suspended') {
            $user = null;
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
