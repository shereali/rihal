<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(120)->by($request->user()?->id ?: $request->ip())
        );
        RateLimiter::for('financial', fn (Request $request) =>
            Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
        );
    }
}
