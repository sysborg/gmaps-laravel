# Cache Strategy

## Goals

- Reduce Google Maps API costs by caching deterministic responses.
- TTL is configurable **per use case** (some data ages faster than others).
- Cache can be disabled globally or per request.
- Uses any Laravel-supported cache driver (Redis, file, database, etc.).

---

## Cache Key Design

Cache key = deterministic hash built from all request parameters.

```
gmaps:{use_case}:{sha256(canonical_params)}
```

Examples:
```
gmaps:nearby_search:a3f8c1d2e4...
gmaps:place_details:7b9e2f4a1c...
```

**`NearbySearchRequest::cacheKey()`** implementation:
```php
public function cacheKey(): string
{
    $params = [
        'lat'       => $this->coordinate->latitude,
        'lng'       => $this->coordinate->longitude,
        'radius'    => $this->radius,
        'type'      => $this->type,
        'keyword'   => $this->keyword,
        'language'  => $this->language,
        'pageToken' => $this->pageToken,
    ];
    ksort($params);  // canonical order

    return 'gmaps:nearby_search:' . hash('sha256', json_encode($params));
}
```

---

## TTL per Use Case

Configured in `config/gmaps.php`:

```php
'cache' => [
    'enabled' => env('GOOGLE_MAPS_CACHE_ENABLED', true),
    'prefix'  => env('GOOGLE_MAPS_CACHE_PREFIX', 'gmaps'),
    'ttl'     => [
        'nearby_search' => env('GOOGLE_MAPS_CACHE_NEARBY_TTL',  3_600),   // 1 hour
        'place_details' => env('GOOGLE_MAPS_CACHE_DETAILS_TTL', 86_400),  // 24 hours
        'text_search'   => env('GOOGLE_MAPS_CACHE_TEXT_TTL',    3_600),   // 1 hour
    ],
],
```

**Rationale:**
| Use case       | Default TTL | Why                                          |
|----------------|-------------|----------------------------------------------|
| nearby_search  | 1 hour      | Places change occasionally, not every minute  |
| place_details  | 24 hours    | Name/address/hours rarely change day-to-day  |
| text_search    | 1 hour      | Same as nearby                               |

---

## Cache Flow in Use Case

```php
// NearbySearchUseCase::execute()

$key = $request->cacheKey();

if ($this->cache->has($key)) {
    return $this->cache->get($key);   // ← cache hit, no API call
}

$response = $this->places->nearbySearch($request);  // ← API call

$this->cache->put($key, $response, $this->cacheConfig->ttlSeconds);

return $response;
```

---

## Disabling Cache

**Globally** — in `.env`:
```
GOOGLE_MAPS_CACHE_ENABLED=false
```

**Per request** — the UseCase receives `CacheConfig` from the container,
which is built from config. To skip cache on a single call, the user can
resolve a fresh use case with a `CacheConfig(enabled: false)`:

```php
// Advanced: bypass cache for a single call
app(NearbySearchUseCase::class, [
    'cacheConfig' => new CacheConfig(enabled: false, ttlSeconds: 0),
])->execute($request);
```

---

## CachePort → LaravelCacheAdapter

```php
// src/Adapters/Cache/LaravelCacheAdapter.php

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class LaravelCacheAdapter implements CachePort
{
    public function __construct(private CacheRepository $cache) {}

    public function get(string $key): mixed        { return $this->cache->get($key); }
    public function put(string $key, mixed $value, int $ttl): void
                                                   { $this->cache->put($key, $value, $ttl); }
    public function forget(string $key): void      { $this->cache->forget($key); }
    public function has(string $key): bool         { return $this->cache->has($key); }
}
```

The service provider resolves the correct cache store based on
`config('gmaps.cache.driver')` — defaults to `'default'` (whatever the
app's default cache driver is).
