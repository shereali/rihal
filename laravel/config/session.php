<?php

return [

    'default' => env('SESSION_DRIVER', 'file'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'driver' => env('SESSION_DRIVER', 'file'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    'lottery' => [1, 100],
];
