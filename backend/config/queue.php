<?php

return [

    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

    ],

    'batching' => [
        'database' => env('QUEUE_BATCHING_TABLE', 'job_batches'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
