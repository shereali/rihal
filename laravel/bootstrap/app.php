<?php

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // Stateless API: clients authenticate with Bearer tokens from
        // localStorage (see nuxt/app/utils/api.ts + plugins/api.client.ts).
        // Sanctum's stateful (cookie + CSRF) SPA mode is NOT used, so the
        // frontend origins must stay OUT of SANCTUM_STATEFUL_DOMAINS.

        $middleware->alias([
            'auth.token' => \App\Http\Middleware\AuthenticateWithToken::class,
            'tenant.scoped' => \App\Http\Middleware\EnsureTenantScoped::class,
            'tenant.context' => \App\Http\Middleware\RequireTenantContext::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
