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
    'google' => [
        'translate_key' => env('GOOGLE_TRANSLATE_KEY'),
    ],

    // Shufti Pro identity verification (selfie + ID document). The keys stay
    // on the server; the app uploads the photos to api/signup or
    // api/identity/verify and never talks to Shufti itself.
    'shufti' => [
        'client_id' => env('SHUFTI_CLIENT_ID'),
        'secret_key' => env('SHUFTI_SECRET_KEY'),
        'base_url' => env('SHUFTI_BASE_URL', 'https://api.shuftipro.com'),
        // Only sent when set. Shufti refuses callbacks to a domain that is
        // not registered in its back office ("callback domain is not
        // registered"), so register carryon.app there first, then set
        // https://carryon.app/admin/api/identity/shufti/callback. Without it,
        // pending checks are resolved by identity:sync-pending.
        'callback_url' => env('SHUFTI_CALLBACK_URL'),
        // Shufti usually answers in ~20 s; past this the attempt is kept as
        // pending and resolved by the callback or identity:sync-pending.
        'timeout' => (int) env('SHUFTI_TIMEOUT', 60),
    ],

    // Mobile API auth. While the old Ionic app (no tokens) is still in use,
    // set API_LEGACY_USER_ID_AUTH=true so its requests keep working on the
    // user ids they send; switch it off once everyone runs the new app.
    'app_api' => [
        'legacy_user_id_auth' => (bool) env('API_LEGACY_USER_ID_AUTH', false),
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS_PATH'),
        // Set FIREBASE_ENABLED=false in a local env so test orders never push
        // notifications to real users' devices.
        'enabled' => env('FIREBASE_ENABLED', true),
    ],

];
