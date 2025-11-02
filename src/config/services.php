<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'default_position' => [
            'lat' => env('GOOGLE_MAPS_DEFAULT_LAT', 35.0238868),
            'lng' => env('GOOGLE_MAPS_DEFAULT_LNG', 135.760201),
        ],
        'marker_styles' => [
            'has_lock' => [
                'background' => env('GOOGLE_MARKER_HAS_LOCK_BACKGROUND', '#FFC107'),
                'borderColor' => env('GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR', '#FFA000'),
                'glyphColor' => env('GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR', '#FFA000'),
            ],
            'no_lock' => [
                'background' => env('GOOGLE_MARKER_NO_LOCK_BACKGROUND', '#4CAF50'),
                'borderColor' => env('GOOGLE_MARKER_NO_LOCK_BORDER_COLOR', '#2E7D32'),
                'glyphColor' => env('GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR', '#2E7D32'),
            ],
            'default' => [
                'background' => env('GOOGLE_MARKER_DEFAULT_BACKGROUND', '#DD0000'),
                'borderColor' => env('GOOGLE_MARKER_DEFAULT_BORDER_COLOR', '#FF0000'),
                'glyphColor' => env('GOOGLE_MARKER_DEFAULT_GLYPH_COLOR', '#FF0000'),
            ],
        ],
    ],

];
