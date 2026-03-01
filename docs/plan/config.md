# Config — gmaps.php

Full specification for `config/gmaps.php`.

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    | Your Google Maps API key. Ensure the key has the following APIs enabled
    | in Google Cloud Console: Places API.
    */
    'api_key' => env('GOOGLE_MAPS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    | IETF language tag for API responses. See Google docs for supported codes.
    | https://developers.google.com/maps/faq#languagesupport
    */
    'language' => env('GOOGLE_MAPS_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => env('GOOGLE_MAPS_HTTP_TIMEOUT', 30),     // seconds
        'retry'   => [
            'times' => env('GOOGLE_MAPS_HTTP_RETRY_TIMES', 3),
            'sleep' => env('GOOGLE_MAPS_HTTP_RETRY_SLEEP', 200), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Strategy
    |--------------------------------------------------------------------------
    | enabled : toggle all caching on/off
    | driver  : any Laravel cache driver name, or 'default' to use app default
    | prefix  : prefix for all cache keys
    | ttl     : per-use-case TTL in seconds (0 = cache disabled for that use case)
    */
    'cache' => [
        'enabled' => env('GOOGLE_MAPS_CACHE_ENABLED', true),
        'driver'  => env('GOOGLE_MAPS_CACHE_DRIVER', 'default'),
        'prefix'  => env('GOOGLE_MAPS_CACHE_PREFIX', 'gmaps'),
        'ttl'     => [
            'nearby_search' => env('GOOGLE_MAPS_CACHE_NEARBY_TTL',  3_600),   // 1 hour
            'place_details' => env('GOOGLE_MAPS_CACHE_DETAILS_TTL', 86_400),  // 24 hours
            'text_search'   => env('GOOGLE_MAPS_CACHE_TEXT_TTL',    3_600),   // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google API Base URLs (override for testing / regional endpoints)
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'places' => env('GOOGLE_MAPS_PLACES_URL', 'https://maps.googleapis.com/maps/api/place'),
    ],

];
```

---

## Required .env variables

```dotenv
GOOGLE_MAPS_API_KEY=your_key_here
```

## Optional .env variables

```dotenv
GOOGLE_MAPS_LANGUAGE=en
GOOGLE_MAPS_CACHE_ENABLED=true
GOOGLE_MAPS_CACHE_DRIVER=redis
GOOGLE_MAPS_CACHE_PREFIX=gmaps
GOOGLE_MAPS_CACHE_NEARBY_TTL=3600
GOOGLE_MAPS_HTTP_TIMEOUT=30
```

---

## Publishing the config

```bash
php artisan vendor:publish --provider="Sysborg\GmapsLaravel\GmapsServiceProvider" --tag="gmaps-config"
```
