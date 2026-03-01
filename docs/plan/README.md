# sysborg/gmaps-laravel — Package Plan

## Overview

A Laravel package for Google Maps APIs built on **Ports and Adapters (Hexagonal Architecture)**,
with a pluggable **cache strategy** to reduce API costs.

## Decisions Made

| Concern         | Decision                                         |
|----------------|--------------------------------------------------|
| Architecture   | Ports and Adapters (Hexagonal)                   |
| APIs (v1)      | Places API — Nearby Search first                 |
| Output format  | Typed DTOs (readonly classes)                    |
| Cache strategy | TTL per use-case, any Laravel cache driver       |
| Laravel        | 11+ / PHP 8.2+                                   |
| HTTP client    | Laravel HTTP (Illuminate\Support\Facades\Http)   |
| Integration    | Facade `GMap::` + Dependency Injection           |
| API keys       | Single key via config / env                      |
| Tests          | PHPUnit + `Http::fake()`                         |
| Language       | English (code, comments, docs)                   |

## First Use Case (today)

**Nearby Places Search**: given a geographic coordinate and a radius (in meters),
return all locations found by Google Places API within that area.

```php
// Via Facade
$results = GMap::nearbySearch(
    coordinate: new Coordinate(-23.5505, -46.6333),
    radius: 1500,
    type: 'restaurant',
);

// Via DI
public function __construct(private readonly PlacesPort $places) {}

$results = $this->places->nearbySearch(new NearbySearchRequest(
    coordinate: new Coordinate(-23.5505, -46.6333),
    radius: 1500,
));
```

## Documents in this directory

| File                        | Content                                 |
|-----------------------------|-----------------------------------------|
| `README.md`                 | This overview                           |
| `architecture.md`           | Full directory structure + layer rules  |
| `implementation-steps.md`   | Step-by-step 3-hour execution plan      |
| `contracts.md`              | All port interfaces with signatures     |
| `dtos.md`                   | All DTOs with properties                |
| `cache-strategy.md`         | Cache design: keys, TTL, invalidation   |
| `config.md`                 | config/gmaps.php full spec              |
