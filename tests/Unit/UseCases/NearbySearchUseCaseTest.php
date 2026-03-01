<?php

namespace Sysborg\GmapsLaravel\Tests\Unit\UseCases;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sysborg\GmapsLaravel\Contracts\Cache\CachePort;
use Sysborg\GmapsLaravel\Contracts\Places\PlacesPort;
use Sysborg\GmapsLaravel\DTOs\Cache\CacheConfig;
use Sysborg\GmapsLaravel\DTOs\Coordinate;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;

class NearbySearchUseCaseTest extends TestCase
{
    private PlacesPort&MockObject $places;
    private CachePort&MockObject  $cache;

    protected function setUp(): void
    {
        $this->places = $this->createMock(PlacesPort::class);
        $this->cache  = $this->createMock(CachePort::class);
    }

    private function makeRequest(): NearbySearchRequest
    {
        return new NearbySearchRequest(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     1500,
            type:       'restaurant',
        );
    }

    private function makeResponse(): NearbySearchResponse
    {
        return new NearbySearchResponse(places: [], status: 'OK');
    }

    public function test_returns_cached_response_without_calling_api(): void
    {
        $request  = $this->makeRequest();
        $response = $this->makeResponse();

        $this->cache->method('has')->with($request->cacheKey())->willReturn(true);
        $this->cache->method('get')->with($request->cacheKey())->willReturn($response);

        $this->places->expects($this->never())->method('nearbySearch');

        $useCase = new NearbySearchUseCase(
            places:      $this->places,
            cache:       $this->cache,
            cacheConfig: new CacheConfig(enabled: true, ttlSeconds: 3600),
        );

        $result = $useCase->execute($request);

        $this->assertSame($response, $result);
    }

    public function test_calls_api_and_stores_in_cache_on_miss(): void
    {
        $request  = $this->makeRequest();
        $response = $this->makeResponse();
        $key      = $request->cacheKey();

        $this->cache->method('has')->with($key)->willReturn(false);
        $this->places->expects($this->once())->method('nearbySearch')->willReturn($response);
        $this->cache->expects($this->once())->method('put')->with($key, $response, 3600);

        $useCase = new NearbySearchUseCase(
            places:      $this->places,
            cache:       $this->cache,
            cacheConfig: new CacheConfig(enabled: true, ttlSeconds: 3600),
        );

        $result = $useCase->execute($request);

        $this->assertSame($response, $result);
    }

    public function test_skips_cache_entirely_when_disabled(): void
    {
        $request  = $this->makeRequest();
        $response = $this->makeResponse();

        $this->cache->expects($this->never())->method('has');
        $this->cache->expects($this->never())->method('put');
        $this->places->expects($this->once())->method('nearbySearch')->willReturn($response);

        $useCase = new NearbySearchUseCase(
            places:      $this->places,
            cache:       $this->cache,
            cacheConfig: new CacheConfig(enabled: false, ttlSeconds: 0),
        );

        $result = $useCase->execute($request);

        $this->assertSame($response, $result);
    }

    public function test_cache_key_is_deterministic(): void
    {
        $a = new NearbySearchRequest(new Coordinate(-23.5505, -46.6333), 1500, 'restaurant');
        $b = new NearbySearchRequest(new Coordinate(-23.5505, -46.6333), 1500, 'restaurant');

        $this->assertSame($a->cacheKey(), $b->cacheKey());
    }

    public function test_cache_key_differs_for_different_params(): void
    {
        $a = new NearbySearchRequest(new Coordinate(-23.5505, -46.6333), 1500, 'restaurant');
        $b = new NearbySearchRequest(new Coordinate(-23.5505, -46.6333), 2000, 'restaurant');

        $this->assertNotSame($a->cacheKey(), $b->cacheKey());
    }
}
