<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key. This will be used to authenticate
    | with the OpenAI API - you can find your key on your OpenAI dashboard.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI Organization. This will be used to
    | authenticate with the OpenAI API - you can find your organization on
    | your OpenAI dashboard.
    |
    */

    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. The default value is 30 seconds.
    |
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
];
