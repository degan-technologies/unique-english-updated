<?php

return [

/*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */ 

    'paths' => ['api/*', 'jitsi/*', 'course/stream/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [env('APP_URL'), 'https://8x8.vc'],

    'allowed_origins_patterns' => ['/^https:\/\/([a-z0-9-]+\.)?8x8\.vc$/'],

    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
