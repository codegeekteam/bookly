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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'payfort' => [
        'sandbox_mode' => env('PAYFORT_SANDBOX_MODE'),
        'access_code' => env('PAYFORT_ACCESS_CODE'),
        'merchant_identifier' => env('PAYFORT_MERCHANT_IDENTIFIER'),
        'request_sha_phrase' => env('PAYFORT_REQUEST_SHA_PHRASE'),
        'response_sha_phrase' => env('PAYFORT_RESPONSE_SHA_PHRASE'),

        'refund_url' => env('REFUND_URL', 'https://sbpaymentservices.payfort.com'),
    ],

];
