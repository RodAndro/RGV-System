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

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.1'),
        'temperature' => env('OLLAMA_TEMPERATURE', 0.7),
        'timeout' => env('OLLAMA_TIMEOUT', 60),
    ],

    'google_drive' => [
        // Preferred: path to a service-account JSON key file on the server.
        'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH'),
        // Alternative: the service-account JSON inlined as a single-line string.
        'credentials_json' => env('GOOGLE_DRIVE_CREDENTIALS_JSON'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        // The email of the employee-facing account used for uploads (for auditing).
        'subject' => env('GOOGLE_DRIVE_SUBJECT'),
    ],

];
