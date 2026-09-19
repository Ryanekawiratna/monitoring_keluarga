<?php

// return [


//     'postmark' => [
//         'key' => env('POSTMARK_API_KEY'),
//     ],

//     'resend' => [
//         'key' => env('RESEND_API_KEY'),
//     ],

//     'ses' => [
//         'key' => env('AWS_ACCESS_KEY_ID'),
//         'secret' => env('AWS_SECRET_ACCESS_KEY'),
//         'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
//     ],

//     'slack' => [
//         'notifications' => [
//             'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
//             'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
//         ],
//     ],

// ];

return [

  'kirimdev' => [
    'api_key'         => env('KIRIMDEV_API_KEY'),
    'phone_number_id' => env('KIRIMDEV_PHONE_NUMBER_ID'),
    'webhook_secret'  => env('KIRIMDEV_WEBHOOK_SECRET'),
  ],

  'gemini' => [
    // Ambil di: aistudio.google.com/apikey
    'api_key' => env('GEMINI_API_KEY'),

    // Model Gemini yang dipakai. Opsi:
    //   gemini-2.0-flash          → cepat, murah, direkomendasikan
    //   gemini-2.0-flash-lite     → paling hemat, untuk traffic tinggi
    //   gemini-1.5-pro            → paling akurat, lebih mahal
    'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
  ],


  'dashboard' => [
    'username' => env('DASHBOARD_USERNAME', 'admin'),
    'password' => env('DASHBOARD_PASSWORD'),
  ],

];
