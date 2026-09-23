<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ProtectLeadData
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLeadRoute = $request->is('api/leads', 'api/leads/*');
        $isStats = $request->is('api/admin/stats');
        if ((! $isLeadRoute && ! $isStats) || ($request->is('api/leads') && $request->isMethod('POST'))) {
            return $next($request);
        }
        $token = $request->bearerToken();
        $userId = $token ? Cache::get('gbrel_auth_token_'.$token) : null;
        $user = $userId ? User::find($userId) : null;
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        if (strtolower($user->status ?? '') === 'suspended') {
            return response()->json(['success' => false, 'message' => 'Account suspended.'], 403);
        }
        $canManage = $user->role === 'admin' || $user->hasPermission('leads.manage');
        $canRead = $canManage || $user->hasPermission('leads.view');
        $allowed = $isStats ? $user->isAdmin() : ($request->isMethod('GET') ? $canRead : $canManage);
        if (! $allowed) {
            return response()->json(['success' => false, 'message' => 'Lead access is not permitted.'], 403);
        }
        $response = $next($request);
        if ($isStats && ! $canRead && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $data['recent_leads'] = [];
            $data['data']['recent_leads'] = [];
            $response->setData($data);
        }

        return $response;
    }
}
