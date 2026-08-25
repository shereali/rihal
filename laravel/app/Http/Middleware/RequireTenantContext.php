<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'এই কার্যক্রমের জন্য প্রতিষ্ঠান নির্বাচন আবশ্যক।',
            ], 403);
        }

        return $next($request);
    }
}
