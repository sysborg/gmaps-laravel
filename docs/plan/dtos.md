# DTOs (Data Transfer Objects)

All DTOs are **readonly classes** (PHP 8.2+). No framework imports.

---

## Coordinate

```php
// src/DTOs/Coordinate.php
readonly class Coordinate
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public function toString(): string  // "lat,lng" format for Google API
    public function toArray(): array    // ['lat' => ..., 'lng' => ...]
}
```

---

## NearbySearchRequest (Input DTO)

```php
// src/DTOs/Places/NearbySearchRequest.php
readonly class NearbySearchRequest
{
    public function __construct(
        public Coordinate $coordinate,
        public int        $radius,           // meters, max 50000
        public ?string    $type     = null,  // e.g. 'restaurant', 'hospital'
        public ?string    $keyword  = null,  // free-text keyword filter
        public string     $language = 'en',
        public ?string    $pageToken = null, // for pagination
    ) {}

    public function cacheKey(): string  // deterministic hash of all params
    public function toQueryParams(): array  // array ready for HTTP GET
}
```

**Google Places Nearby Search parameters:**

| DTO property   | API param     | Notes                              |
|----------------|---------------|------------------------------------|
| `coordinate`   | `location`    | `"lat,lng"` string                 |
| `radius`       | `radius`      | integer, 1–50000 meters            |
| `type`         | `type`        | single type filter                 |
| `keyword`      | `keyword`     | keyword filter                     |
| `language`     | `language`    | IETF language tag                  |
| `pageToken`    | `pagetoken`   | next page token from previous call |

---

## PlaceResult (Single Item DTO)

```php
// src/DTOs/Places/PlaceResult.php
readonly class PlaceResult
{
    public function __construct(
        public string     $placeId,
        public string     $name,
        public string     $vicinity,      // formatted short address
        public Coordinate $location,
        public array      $types,         // e.g. ['restaurant', 'food']
        public ?float     $rating,
        public ?int       $userRatingsTotal,
        public bool       $openNow,
        public ?string    $icon,
        public ?string    $businessStatus, // OPERATIONAL | CLOSED_TEMPORARILY | etc.
    ) {}

    public function toArray(): array
}
```

---

## NearbySearchResponse (Output DTO)

```php
// src/DTOs/Places/NearbySearchResponse.php
readonly class NearbySearchResponse
{
    public function __construct(
        /** @var PlaceResult[] */
        public array   $places,
        public string  $status,           // OK | ZERO_RESULTS | OVER_QUERY_LIMIT | etc.
        public ?string $nextPageToken,    // present when more results available
    ) {}

    public function isEmpty(): bool
    public function hasNextPage(): bool
    public function toArray(): array
}
```

---

## CacheConfig (Value Object — internal)

```php
// src/DTOs/Cache/CacheConfig.php
readonly class CacheConfig
{
    public function __construct(
        public bool   $enabled,
        public int    $ttlSeconds,
        public string $prefix = 'gmaps',
    ) {}
}
```
