<?php

return [
    /*
    |--------------------------------------------------------------------------
    |   Zaps
    |--------------------------------------------------------------------------
    |
    |
    */
    'zaps' => [
        'url' => env('ZAPIER_ZAPS_URL', ''),
        'group-id' => env('ZAPIER_ZAPS_GROUP_ID', ''),
        'hooks' => env('ZAPIER_HOOKS', []),
        'global-data' => [
            'querystring' => [
                // querystring global params for all hooks
            ],
            'body' => [
                // body global params for all hooks
            ]
        ]
    ]
];
