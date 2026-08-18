<?php

return [

    'enabled' => env('RIHAL_TENANCY_ENABLED', true),

    'tenancy_model' => \App\Models\Tenant::class,

    'user_model' => \App\Models\User::class,

    'scoping' => [
        'enabled' => env('RIHAL_TENANCY_SCOPING_ENABLED', true),
        'global_scope' => \App\Scopes\TenantScope::class,
        'middleware' => \App\Http\Middleware\EnsureTenantScoped::class,
    ],

    'multi_tenancy_strategy' => 'single_database',

    'shared_tables' => [
        'users',
        'roles',
        'permissions',
    ],

    'tenant_tables_prefix' => '',
];
