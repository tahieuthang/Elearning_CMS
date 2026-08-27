<?php

use Illuminate\Support\Str;

$defaultSupervisor = [
    'connection' => 'redis',
    'queue' => ['default'],
    'balance' => 'simple',
    'maxProcesses' => 1,
    'maxTime' => 0,
    'maxJobs' => 0,
    'memory' => 128,
    'tries' => 3,
    'timeout' => 600,
    'nice' => 0,
];

$uploadsSupervisor = [
    'connection' => 'redis',
    'queue' => ['uploads'],
    'balance' => 'simple',
    'maxProcesses' => 1,
    'maxTime' => 0,
    'maxJobs' => 0,
    'memory' => 128,
    'tries' => 3,
    'timeout' => 600,
    'nice' => 0,
];

$emailsSupervisor = [
    'connection' => 'redis',
    'queue' => ['emails'],
    'balance' => 'simple',
    'maxProcesses' => 1,
    'maxTime' => 0,
    'maxJobs' => 0,
    'memory' => 128,
    'tries' => 3,
    'timeout' => 120,
    'nice' => 0,
];

$notificationsSupervisor = [
    'connection' => 'redis',
    'queue' => ['notifications'],
    'balance' => 'simple',
    'maxProcesses' => 1,
    'maxTime' => 0,
    'maxJobs' => 0,
    'memory' => 128,
    'tries' => 3,
    'timeout' => 120,
    'nice' => 0,
];

return [
    'name' => env('HORIZON_NAME'),
    'domain' => env('HORIZON_DOMAIN'),
    'path' => env('HORIZON_PATH', 'horizon'),
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'),
    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
        'redis:uploads' => 60,
        'redis:emails' => 60,
        'redis:notifications' => 60,
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'silenced' => [],
    'silenced_tags' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,
    'memory_limit' => 64,

    'defaults' => [],

    'environments' => [
        'production' => [
            'production-supervisor' => $defaultSupervisor,
            'uploads-supervisor' => $uploadsSupervisor,
            'emails-supervisor' => $emailsSupervisor,
            'notifications-supervisor' => $notificationsSupervisor,
        ],
        'local' => [
            'local-supervisor' => $defaultSupervisor,
            'uploads-supervisor' => $uploadsSupervisor,
            'emails-supervisor' => $emailsSupervisor,
            'notifications-supervisor' => $notificationsSupervisor,
        ],
    ],
];
