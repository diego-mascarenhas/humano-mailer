<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Providers
    |--------------------------------------------------------------------------
    |
    | Configure the available email providers for the mailer system
    |
    */
    'providers' => [
        'smtp' => [
            'name' => 'SMTP',
            'enabled' => true,
        ],
        'api' => [
            'name' => 'Email API',
            'enabled' => !empty(env('MAIL_API_KEY')),
            'key' => env('MAIL_API_KEY'),
            'domain' => env('MAIL_API_DOMAIN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Campaign Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for email campaigns
    |
    */
    'campaigns' => [
        'default_min_hours_between_emails' => 48,
        'max_retries' => 3,
        'batch_size' => 100,
        'rate_limit_per_minute' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking Settings
    |--------------------------------------------------------------------------
    |
    | Configure email tracking features
    |
    */
    'tracking' => [
        'open_tracking' => true,
        'click_tracking' => true,
        'unsubscribe_tracking' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Domains
    |--------------------------------------------------------------------------
    |
    | Email domains to exclude from campaigns (test/demo addresses)
    |
    */
    'excluded_domains' => [
        '@example.org',
        '@example.net',
        '@example.com',
        '@demo.com',
        '@test.com',
        '@localhost',
        '@testing.com',
        '@dummy.com',
        '@fake.com',
    ],
];
