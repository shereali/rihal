<?php

return [

    'app' => [
        'name' => env('APP_NAME', 'Rihal'),
        'env' => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://localhost'),
        'timezone' => 'Asia/Dhaka',
        'locale' => 'bn',
        'fallback_locale' => 'bn',
        'faker_locale' => 'bn_BD',
        'cipher' => 'AES-256-CBC',
        'key' => env('APP_KEY'),
        'previous_keys' => [env('APP_PREVIOUS_KEYS') ?: ''],
        'maintenance' => [
            'driver' => 'file',
        ],
        'providers' => [
            \App\Providers\AppServiceProvider::class,
        ],
    ],

    'providers' => [
        // Laravel Framework Service Providers
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        // Third-party
        Laravel\Sanctum\SanctumServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Spatie\Activitylog\ActivitylogServiceProvider::class,

        // Application
        App\Providers\AppServiceProvider::class,
    ],
];
