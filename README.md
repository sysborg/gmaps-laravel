# sysborg/gmaps-laravel

> A Google Maps API package for Laravel built on **Ports and Adapters (Hexagonal Architecture)** — clean, testable, and designed to grow with your needs.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%2B-red)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)](#testing)

---

## Why this package?

Most Google Maps packages for Laravel expose raw arrays and mix API calls directly into your business logic. This package takes a different approach:

- **Ports and Adapters** — your domain code never talks to Google directly; it depends on interfaces
- **Typed DTOs** — `readonly` classes with full IDE autocomplete, no more guessing array keys
- **Built-in cache strategy** — configurable TTL per use case to save money on API calls
- **Facade + DI** — use whichever style fits your team

---

## Installation

```bash
composer require sysborg/gmaps-laravel
```

Publish the config:

```bash
php artisan vendor:publish --provider="Sysborg\GmapsLaravel\GmapsServiceProvider" --tag="gmaps-config"
```

Add your API key to `.env`:

```dotenv
GOOGLE_MAPS_API_KEY=your_key_here
```

> Make sure the **Places API** is enabled in your [Google Cloud Console](https://console.cloud.google.com).

---

## Usage

### Nearby Places Search

Find all locations within a radius from a geographic point.

**Via Facade:**

```php
use Sysborg\GmapsLaravel\Facades\GMap;
use Sysborg\GmapsLaravel\DTOs\Coordinate;

$response = GMap::nearbySearch(
    coordinate: new Coordinate(-23.5505, -46.6333),
    radius:     1500,           // meters
    type:       'restaurant',   // optional
    keyword:    'pizza',        // optional
);

foreach ($response->places as $place) {
    echo $place->name;           // "Pizzaria Italia"
    echo $place->vicinity;       // "Rua Augusta, 100"
    echo $place->rating;         // 4.5
    echo $place->location->latitude;
    echo $place->location->longitude;
}

if ($response->hasNextPage()) {
    // fetch next page using $response->nextPageToken
}
```

**Via Dependency Injection:**

```php
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;
use Sysborg\GmapsLaravel\DTOs\Coordinate;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;

class FindNearbyRestaurantsAction
{
    public function __construct(private readonly NearbySearchUseCase $useCase) {}

    public function handle(float $lat, float $lng): array
    {
        $response = $this->useCase->execute(new NearbySearchRequest(
            coordinate: new Coordinate($lat, $lng),
            radius:     1500,
            type:       'restaurant',
        ));

        return $response->toArray();
    }
}
```

---

## Cache Strategy

Every API call is cached by default. The cache key is a deterministic hash of all request parameters — same input always hits the cache.

```dotenv
# Toggle cache on/off
GOOGLE_MAPS_CACHE_ENABLED=true

# Use any Laravel cache driver
GOOGLE_MAPS_CACHE_DRIVER=redis

# TTL per use case (seconds)
GOOGLE_MAPS_CACHE_NEARBY_TTL=3600
```

Full config reference at [`config/gmaps.php`](config/gmaps.php).

---

## Architecture

```
src/
├── Contracts/          ← Ports (pure PHP interfaces, zero framework imports)
│   ├── Cache/CachePort.php
│   ├── Http/HttpClientPort.php
│   └── Places/PlacesPort.php
├── DTOs/               ← Immutable readonly value objects
│   ├── Coordinate.php
│   └── Places/  NearbySearchRequest, PlaceResult, NearbySearchResponse
├── UseCases/           ← Application layer (depends only on Ports + DTOs)
│   └── Places/NearbySearchUseCase.php
├── Adapters/           ← Infrastructure (implements Ports using Laravel/Guzzle)
│   ├── Cache/LaravelCacheAdapter.php
│   ├── Http/LaravelHttpAdapter.php
│   └── Places/GooglePlacesAdapter.php
├── Facades/GMap.php
└── GmapsServiceProvider.php
```

---

## Testing

```bash
composer install
./vendor/bin/phpunit
```

All tests use `Http::fake()` — no real API calls are made.

```
OK (11 tests, 30 assertions)
```

---

## Currently Implemented

| Feature | Status |
|---|---|
| Places — Nearby Search | ✅ |
| Cache with TTL per use case | ✅ |
| Facade + DI integration | ✅ |
| Typed DTOs (readonly) | ✅ |
| PHPUnit tests with Http::fake() | ✅ |

---

## Roadmap — Have a need? Open an issue!

This package is built to grow. The architecture makes it straightforward to add new APIs without touching existing code. If you need any of the below — or something not listed — **open an issue and describe your use case**. Contributions are welcome.

| Feature | Notes |
|---|---|
| Places — Text Search | Search by keyword anywhere |
| Places — Place Details | Full details by `place_id` |
| Places — Autocomplete | For address/search inputs |
| Geocoding API | Address ↔ coordinates |
| Reverse Geocoding | Coordinates → formatted address |
| Directions API | Routes with waypoints |
| Distance Matrix | Multi-origin/destination travel time |
| Static Maps | Generate map image URLs |
| Elevation API | Altitude for coordinates |
| Time Zone API | Timezone from coordinates |
| Roads API | Snap-to-road, speed limits |
| Pagination helper | Auto-fetch next pages |
| Multi-page collector | Merge all paginated results automatically |

> **Have a specific need?** [Open an issue](https://github.com/andmarruda/gmaps-laravel/issues) describing what you need and how you plan to use it. The more context you give, the faster it gets implemented.

---

## Contributing

1. Fork the repository
2. Create a branch: `git checkout -b feat/your-feature`
3. Follow the existing patterns — one port, one adapter, one use case (see [`docs/plan/architecture.md`](docs/plan/architecture.md))
4. Write tests with `Http::fake()`
5. Open a PR

---

## Support the project

If this package saves you time or reduces your Google Maps API costs, consider buying a coffee.
It helps keep this maintained and motivates new features to be added.

[![Buy Me a Coffee](https://img.shields.io/badge/Buy%20Me%20a%20Coffee-%E2%98%95%20Support%20the%20project-yellow?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/andmarruda)

---

## License

MIT © [Anderson Arruda](https://github.com/andmarruda)
