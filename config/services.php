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

    'afromessage' => [
        'api_url'       => env('AFROMESSAGE_API_URL'),
        'api_token'     => env('AFROMESSAGE_API_TOKEN'),
        'identifier_id' => env('AFROMESSAGE_IDENTIFIER_ID'),
        'sender_name'   => env('AFROMESSAGE_SENDER_NAME'),
        'verifyUrl'     =>env('AFROMESSAGE_VERIFY_URL'),
        'security_url' => env('AFROMESSAGE_OTP_URL'),
    ],

    
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],
    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT_URI'),
    ],
    'linkedin' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_REDIRECT_URI'),
    ],

    'jitsi' => [
        'app_id' => env('JITSI_APP_ID'),
        'sub' => env('JITSI_SUB_DOMAIN'),
        'private_key_path' => env('JITSI_PRIVATE_KEY_PATH', 'jaas_private_key.pk'),
        'private_key_fullpath' => storage_path(
            'app/keys/' . env('JITSI_PRIVATE_KEY_PATH', 'jaas_private_key.pk')
        ),
        'kid' => env('JITSI_KID'),
        'algorithm' => 'RS256',
        'aud'  => 'jitsi',
    ],

    'chapa' => [
        'secret_key' => env('CHAPA_SECRET_KEY'),
        'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.com/v1'),
        'approval_secret' => env('TRANSFER_APPROVAL_SECRET'),
        'webhook_secret' => env('WEBHOOK_SECRET'),
    ],

];
