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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | SMS Gateway Configuration
     |--------------------------------------------------------------------------
     |
     | Configure the SMS gateway used for sending transactional SMS such as
     | OTPs. The "driver" option controls the default gateway. Supported
     | drivers: "way2mint", "log".
     |
     */

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),

        'way2mint' => [
            'base_url' => env('WAY2MINT_BASE_URL', 'https://apibulksms.way2mint.com'),
            'username' => env('WAY2MINT_USERNAME'),
            'password' => env('WAY2MINT_PASSWORD'),
            'sender_id' => env('WAY2MINT_SENDER_ID', 'KUTOOT'),
            'pe_id' => env('WAY2MINT_PE_ID'),
            'provider_pe_id' => env('WAY2MINT_PROVIDER_PE_ID'),
            'otp_template_id' => env('WAY2MINT_OTP_TEMPLATE_ID'),
            'timeout' => env('WAY2MINT_TIMEOUT', 30),
            'retry_attempts' => env('WAY2MINT_RETRY_ATTEMPTS', 3),
            'retry_delay_ms' => env('WAY2MINT_RETRY_DELAY_MS', 500),
        ],
    ],

];
