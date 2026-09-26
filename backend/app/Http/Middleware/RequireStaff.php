<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only GBREL staff. Optional permission slugs are alternatives: staff:properties.edit,properties.verify_rajuk
 * lets through anyone holding either permission.
 */
class RequireStaff
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Please sign in to continue.'], 401);
        }

        $allowed = $user->isStaff()
            && ($permissions === [] || collect($permissions)->contains(fn (string $slug): bool => $user->hasPermission($slug)));

        if (! $allowed) {
            return response()->json(['success' => false, 'message' => 'You do not have permission for this action.'], 403);
        }

        return $next($request);
    }
}
