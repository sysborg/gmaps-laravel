# Implementation Steps — 3-Hour Plan

Target: fully working `NearbySearchUseCase` with cache, tests, Facade + DI integration.

---

## Hour 1 — Foundation (0:00–1:00)

### Step 1 — composer.json (15 min)

Update `composer.json`:
- `require`: `php ^8.2`, `illuminate/support ^11.0`, `illuminate/http ^11.0`, `illuminate/cache ^11.0`
- `require-dev`: `phpunit/phpunit ^11.0`, `orchestra/testbench ^9.0`
- `autoload`: `Sysborg\\GmapsLaravel\\` → `src/`
- `extra.laravel`: register `GmapsServiceProvider`

### Step 2 — Ports / Contracts (20 min)

Create 3 port interfaces (pure PHP, no framework imports):
- `src/Contracts/Cache/CachePort.php`
- `src/Contracts/Http/HttpClientPort.php`
- `src/Contracts/Places/PlacesPort.php`

### Step 3 — DTOs (25 min)

Create all readonly DTOs:
- `src/DTOs/Coordinate.php`
- `src/DTOs/Cache/CacheConfig.php`
- `src/DTOs/Places/NearbySearchRequest.php`  ← includes `cacheKey()` and `toQueryParams()`
- `src/DTOs/Places/PlaceResult.php`
- `src/DTOs/Places/NearbySearchResponse.php`

---

## Hour 2 — Adapters + Use Case (1:00–2:00)

### Step 4 — CachePort Adapter (15 min)

`src/Adapters/Cache/LaravelCacheAdapter.php`
- Constructor receives `Illuminate\Contracts\Cache\Repository`
- Implements `get`, `put`, `forget`, `has`

### Step 5 — HttpClientPort Adapter (15 min)

`src/Adapters/Http/LaravelHttpAdapter.php`
- Constructor receives timeout and retry config
- Uses `Http::withOptions()->retry()->get()`
- Throws `\RuntimeException` on non-200 or error status

### Step 6 — GooglePlacesAdapter (30 min)

`src/Adapters/Places/GooglePlacesAdapter.php`
- Implements `PlacesPort`
- Constructor receives `HttpClientPort` + api_key + base URL
- `nearbySearch()`: builds query params, calls HTTP, maps JSON → DTOs
- Handles Google API error statuses (`ZERO_RESULTS`, `REQUEST_DENIED`, etc.)

---

## Hour 3 — Integration + Tests (2:00–3:00)

### Step 7 — NearbySearchUseCase (15 min)

`src/UseCases/Places/NearbySearchUseCase.php`
- Constructor: `PlacesPort $places, CachePort $cache, CacheConfig $cacheConfig`
- `execute(NearbySearchRequest $request): NearbySearchResponse`
- Cache check → API call → cache store (see `cache-strategy.md`)

### Step 8 — ServiceProvider (20 min)

`src/GmapsServiceProvider.php`
- `register()`: bind all ports to adapters using `$this->app->bind()`
- `boot()`: `$this->publishes([...], 'gmaps-config')`
- Wire: `CachePort` → `LaravelCacheAdapter` (using configured driver)
- Wire: `HttpClientPort` → `LaravelHttpAdapter`
- Wire: `PlacesPort` → `GooglePlacesAdapter`
- Register Facade alias `GMap`

### Step 9 — Facade (5 min)

`src/Facades/GMap.php`
- Extends `Illuminate\Support\Facades\Facade`
- `getFacadeAccessor()` returns `'gmaps.places'`

### Step 10 — Tests (20 min)

**Unit test** — `tests/Unit/UseCases/NearbySearchUseCaseTest.php`
- Mock `PlacesPort` and `CachePort`
- Assert cache hit skips API call
- Assert cache miss calls API then stores result

**Feature test** — `tests/Feature/Places/NearbySearchTest.php`
- `Http::fake()` with a realistic Google API JSON fixture
- Assert `NearbySearchResponse` is returned correctly
- Assert correct URL and query params were called

---

## Verification Checklist

- [ ] `composer install` succeeds
- [ ] `php artisan vendor:publish --tag=gmaps-config` works
- [ ] `GMap::nearbySearch(...)` resolves via Facade
- [ ] `NearbySearchUseCase` can be injected via DI
- [ ] Cache hit skips HTTP call (verified in unit test)
- [ ] All PHPUnit tests pass

---

## File Creation Order (dependency-safe)

```
1. composer.json
2. src/Contracts/Cache/CachePort.php
3. src/Contracts/Http/HttpClientPort.php
4. src/Contracts/Places/PlacesPort.php
5. src/DTOs/Coordinate.php
6. src/DTOs/Cache/CacheConfig.php
7. src/DTOs/Places/NearbySearchRequest.php
8. src/DTOs/Places/PlaceResult.php
9. src/DTOs/Places/NearbySearchResponse.php
10. src/Adapters/Cache/LaravelCacheAdapter.php
11. src/Adapters/Http/LaravelHttpAdapter.php
12. src/Adapters/Places/GooglePlacesAdapter.php
13. src/UseCases/Places/NearbySearchUseCase.php
14. src/Facades/GMap.php
15. src/GmapsServiceProvider.php
16. config/gmaps.php
17. tests/Unit/UseCases/NearbySearchUseCaseTest.php
18. tests/Feature/Places/NearbySearchTest.php
```
