<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // Conversions API: sends Lead / QualifiedLead from the server too. The pixel ID itself is set in Admin → Settings.
    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
        'capi_token' => env('META_CAPI_TOKEN'),
        'test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
        // Lead Ads webhook (/api/meta/leadgen): instant-form leads go straight into the CRM.
        'app_secret' => env('META_APP_SECRET'),
        'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'page_access_token' => env('META_PAGE_ACCESS_TOKEN'),
        'graph_version' => env('META_GRAPH_VERSION', 'v26.0'),
    ],

    // EasyPay (bKash/Nagad/Rocket) for the paid e-book on /book. API key: EasyPay Merchant Dashboard → API Settings.
    'easypay' => [
        'base_url' => env('EASYPAY_BASE_URL', 'https://www.easypaybd.online/api'),
        'api_key' => env('EASYPAY_API_KEY'),
        // Public site address used in the callback and webhook links (defaults to APP_URL).
        'site_url' => env('EASYPAY_SITE_URL'),
    ],

    // The e-book's selling price in BDT. The list price is only shown on the page (struck through).
    'book' => [
        'price' => (int) env('BOOK_PRICE', 500),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
