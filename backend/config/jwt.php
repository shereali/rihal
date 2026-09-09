<?php

return [

    'default' => env('JWT_SECRET'),

    'algorithm' => 'HS256',

    'ttl' => 60, // minutes

    'refresh_ttl' => 20160, // minutes

    'public_key' => env('JWT_PUBLIC_KEY'),

    'private_key' => env('JWT_PRIVATE_KEY'),
];
