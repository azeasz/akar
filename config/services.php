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

    'iucn' => [
        'key' => env('IUCN_API_TOKEN'),
    ],

    'speciesplus' => [
        'token' => env('CITES_API_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
        'fobi_api' => [
        'base_url' => env('FOBI_API_BASE_URL', 'https://amaturalist.com'),
        'token' => env('FOBI_API_TOKEN'),
        'timeout' => env('FOBI_API_TIMEOUT', 30),
        'cache_ttl' => env('FOBI_API_CACHE_TTL', 300), // 5 minutes default
    ],


];
