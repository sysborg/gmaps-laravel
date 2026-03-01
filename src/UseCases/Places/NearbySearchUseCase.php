<?php

namespace Sysborg\GmapsLaravel\UseCases\Places;

use Sysborg\GmapsLaravel\Contracts\Cache\CachePort;
use Sysborg\GmapsLaravel\Contracts\Places\PlacesPort;
use Sysborg\GmapsLaravel\DTOs\Cache\CacheConfig;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;

class NearbySearchUseCase
{
    public function __construct(
        private readonly PlacesPort  $places,
        private readonly CachePort   $cache,
        private readonly CacheConfig $cacheConfig,
    ) {}

    public function execute(NearbySearchRequest $request): NearbySearchResponse
    {
        if ($this->cacheConfig->enabled) {
            $key = $request->cacheKey();

            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }

            $response = $this->places->nearbySearch($request);

            $this->cache->put($key, $response, $this->cacheConfig->ttlSeconds);

            return $response;
        }

        return $this->places->nearbySearch($request);
    }
}
