<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks requests that are not signed in.
 */
class RequireApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['success' => false, 'message' => 'Please sign in to continue.'], 401);
        }

        return $next($request);
    }
}
