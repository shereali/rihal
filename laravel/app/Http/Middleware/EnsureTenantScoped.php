<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScoped
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->route('tenant_id');

        if ($tenantId) {
            $user = $request->user();
            if ($user && $user->tenant_id !== (int) $tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. This resource belongs to another tenant.',
                ], 403);
            }
        }

        return $next($request);
    }
}
