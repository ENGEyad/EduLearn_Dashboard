<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This value controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this in
    | your ".env" file (BROADCAST_CONNECTION).
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key'    => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),

            'options' => [
                'host'   => env('REVERB_HOST', 'localhost'),
                'port'   => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),

                // مهم جداً مع HTTP محلياً
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',

                // لو عندك ws path مخصص فعّله:
                'path'   => env('REVERB_SERVER_PATH', ''),
            ],
        ],

        // احتياطي (اختياري)
        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
