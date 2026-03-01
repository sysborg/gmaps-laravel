# Contracts (Ports)

All interfaces live under `src/Contracts/`. They have zero framework imports.

---

## PlacesPort

```php
// src/Contracts/Places/PlacesPort.php
namespace Sysborg\GmapsLaravel\Contracts\Places;

use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;

interface PlacesPort
{
    public function nearbySearch(NearbySearchRequest $request): NearbySearchResponse;
}
```

---

## CachePort

```php
// src/Contracts/Cache/CachePort.php
namespace Sysborg\GmapsLaravel\Contracts\Cache;

interface CachePort
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value, int $ttlSeconds): void;

    public function forget(string $key): void;

    public function has(string $key): bool;
}
```

---

## HttpClientPort

```php
// src/Contracts/Http/HttpClientPort.php
namespace Sysborg\GmapsLaravel\Contracts\Http;

interface HttpClientPort
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $url, array $query = []): array;
}
```

---

## Notes

- `PlacesPort` is the **primary port** (driven by the application).
- `CachePort` and `HttpClientPort` are **secondary ports** (used by adapters).
- Adding a new API (Geocoding, Directions, etc.) means adding a new port interface and a new use case — the rest of the infrastructure stays unchanged.
