<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    | Ensure the Places API is enabled for this key in Google Cloud Console.
    */
    'api_key' => env('GOOGLE_MAPS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    | IETF language tag. See: https://developers.google.com/maps/faq#languagesupport
    */
    'language' => env('GOOGLE_MAPS_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout'   => (int) env('GOOGLE_MAPS_HTTP_TIMEOUT', 30),
        'retry'     => [
            'times' => (int) env('GOOGLE_MAPS_HTTP_RETRY_TIMES', 3),
            'sleep' => (int) env('GOOGLE_MAPS_HTTP_RETRY_SLEEP', 200),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Strategy
    |--------------------------------------------------------------------------
    | enabled : toggle all caching globally
    | driver  : any Laravel cache driver name, or 'default' to use app default
    | prefix  : prefix prepended to all cache keys
    | ttl     : per-use-case TTL in seconds
    */
    'cache' => [
        'enabled' => (bool) env('GOOGLE_MAPS_CACHE_ENABLED', true),
        'driver'  => env('GOOGLE_MAPS_CACHE_DRIVER', 'default'),
        'prefix'  => env('GOOGLE_MAPS_CACHE_PREFIX', 'gmaps'),
        'ttl'     => [
            'nearby_search' => (int) env('GOOGLE_MAPS_CACHE_NEARBY_TTL',  3_600),
            'place_details' => (int) env('GOOGLE_MAPS_CACHE_DETAILS_TTL', 86_400),
            'text_search'   => (int) env('GOOGLE_MAPS_CACHE_TEXT_TTL',    3_600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google API Base URLs
    |--------------------------------------------------------------------------
    | Override for testing or regional endpoints.
    */
    'endpoints' => [
        'places' => env('GOOGLE_MAPS_PLACES_URL', 'https://maps.googleapis.com/maps/api/place'),
    ],

];
