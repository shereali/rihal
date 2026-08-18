<?php

return [

    'default' => env('BROADCAST_DRIVER', 'log'),

    'connections' => [
        'log' => [
            'driver' => 'log',
        ],
    ],

    'channels' => [
        ' 통보했다' => [
            'driver' => '방송',
        ],
    ],
];
