# Architecture — Ports and Adapters

## Layer Rules

```
Domain (DTOs, Ports)       ← no framework dependencies
    ↑
Application (Use Cases)    ← depends only on Ports + DTOs
    ↑
Infrastructure (Adapters)  ← implements Ports, uses Laravel/Guzzle
    ↑
Framework (ServiceProvider, Facade, Config) ← wires everything together
```

**Rule**: inner layers NEVER import outer layers.
The `Domain` and `Application` layers have zero Laravel imports.

---

## Directory Structure

```
gmaps-laravel/
├── composer.json
├── config/
│   └── gmaps.php                         # Published config
├── docs/
│   └── plan/                             # This planning directory
├── src/
│   ├── Contracts/                        # PORTS (interfaces = domain boundary)
│   │   ├── Cache/
│   │   │   └── CachePort.php
│   │   ├── Http/
│   │   │   └── HttpClientPort.php
│   │   └── Places/
│   │       └── PlacesPort.php
│   │
│   ├── DTOs/                             # Immutable value objects
│   │   ├── Coordinate.php                # lat + lng
│   │   ├── Places/
│   │   │   ├── NearbySearchRequest.php   # Input DTO
│   │   │   ├── PlaceResult.php           # Single place item
│   │   │   └── NearbySearchResponse.php  # Output DTO (collection of PlaceResult)
│   │   └── Cache/
│   │       └── CacheConfig.php           # TTL config value object
│   │
│   ├── UseCases/                         # APPLICATION LAYER
│   │   └── Places/
│   │       └── NearbySearchUseCase.php   # Orchestrates Port + Cache
│   │
│   ├── Adapters/                         # INFRASTRUCTURE LAYER (implements Ports)
│   │   ├── Cache/
│   │   │   └── LaravelCacheAdapter.php   # Wraps Illuminate\Cache
│   │   ├── Http/
│   │   │   └── LaravelHttpAdapter.php    # Wraps Illuminate\Http\Client
│   │   └── Places/
│   │       └── GooglePlacesAdapter.php   # Calls Google Places API
│   │
│   ├── Facades/
│   │   └── GMap.php                      # Laravel Facade
│   │
│   └── GmapsServiceProvider.php          # Binds ports → adapters in container
│
└── tests/
    ├── Unit/
    │   ├── DTOs/
    │   │   └── CoordinateTest.php
    │   └── UseCases/
    │       └── NearbySearchUseCaseTest.php
    └── Feature/
        └── Places/
            └── NearbySearchTest.php      # Http::fake() integration test
```

---

## Port Responsibilities

| Port              | Lives in               | Implemented by                   |
|-------------------|------------------------|----------------------------------|
| `PlacesPort`      | `Contracts/Places/`    | `GooglePlacesAdapter`            |
| `CachePort`       | `Contracts/Cache/`     | `LaravelCacheAdapter`            |
| `HttpClientPort`  | `Contracts/Http/`      | `LaravelHttpAdapter`             |

---

## Data Flow: NearbySearch

```
HTTP Request (Laravel)
    │
    ▼
Controller / Job
    │  injects
    ▼
NearbySearchUseCase
    │
    ├─── CachePort::get(key)  ──► hit?  ──► return cached NearbySearchResponse
    │
    ├─── PlacesPort::nearbySearch(NearbySearchRequest)
    │         │
    │         ▼
    │    GooglePlacesAdapter
    │         │  uses HttpClientPort
    │         ▼
    │    LaravelHttpAdapter ──► Google Places API (HTTPS)
    │         │
    │         ▼
    │    maps JSON → NearbySearchResponse (DTOs)
    │
    └─── CachePort::put(key, response, ttl)
              │
              ▼
         LaravelCacheAdapter (Redis / file / database)
```
