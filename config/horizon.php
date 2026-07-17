<?php

return [
    'use' => env('HORIZON_USE', 'default'),

    'environments' => [
        'production' => [
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default', 'emails', 'cars'],
                'balance' => 'simple',
                'processes' => 10,
                'tries' => 3,
            ],
            'supervisor-cars' => [
                'connection' => 'redis',
                'queue' => ['cars'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 3,
            ],
        ],

        'local' => [
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default', 'cars'],
                'balance' => 'auto',
                'processes' => 1,
                'tries' => 3,
            ],
        ],
    ],
];
