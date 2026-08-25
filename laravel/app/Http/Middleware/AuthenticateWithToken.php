<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.',
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($request->bearerToken());
        $user = $accessToken?->tokenable;
        $configuredExpiration = config('sanctum.expiration');
        $expired = $accessToken && (
            ($accessToken->expires_at && $accessToken->expires_at->isPast()) ||
            ($configuredExpiration !== null && $accessToken->created_at?->lte(now()->subMinutes((int) $configuredExpiration)))
        );

        if (!$user || $expired) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
            ], 401);
        }

        $user->withAccessToken($accessToken);
        $request->setUserResolver(fn () => $user);
        $accessToken->forceFill(['last_used_at' => now()])->save();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated.',
            ], 403);
        }

        return $next($request);
    }
}
