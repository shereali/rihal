<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimStrings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(
            collect($request->all())
                ->map(function ($value) {
                    return is_string($value) ? trim($value) : $value;
                })
                ->toArray()
        );

        return $next($request);
    }
}
