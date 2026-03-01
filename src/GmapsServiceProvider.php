<?php

namespace Sysborg\GmapsLaravel;

use Illuminate\Support\ServiceProvider;
use Sysborg\GmapsLaravel\Adapters\Cache\LaravelCacheAdapter;
use Sysborg\GmapsLaravel\Adapters\Http\LaravelHttpAdapter;
use Sysborg\GmapsLaravel\Adapters\Places\GooglePlacesAdapter;
use Sysborg\GmapsLaravel\Contracts\Cache\CachePort;
use Sysborg\GmapsLaravel\Contracts\Http\HttpClientPort;
use Sysborg\GmapsLaravel\Contracts\Places\PlacesPort;
use Sysborg\GmapsLaravel\DTOs\Cache\CacheConfig;
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;

class GmapsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gmaps.php', 'gmaps');

        $this->app->bind(HttpClientPort::class, function () {
            return new LaravelHttpAdapter(
                timeout:      config('gmaps.http.timeout'),
                retryTimes:   config('gmaps.http.retry.times'),
                retrySleepMs: config('gmaps.http.retry.sleep'),
            );
        });

        $this->app->bind(CachePort::class, function () {
            $driver = config('gmaps.cache.driver', 'default');
            $store  = $driver === 'default'
                ? $this->app['cache']->store()
                : $this->app['cache']->store($driver);

            return new LaravelCacheAdapter($store);
        });

        $this->app->bind(PlacesPort::class, function () {
            return new GooglePlacesAdapter(
                http:    $this->app->make(HttpClientPort::class),
                apiKey:  config('gmaps.api_key'),
                baseUrl: config('gmaps.endpoints.places'),
            );
        });

        $this->app->bind(NearbySearchUseCase::class, function () {
            return new NearbySearchUseCase(
                places:      $this->app->make(PlacesPort::class),
                cache:       $this->app->make(CachePort::class),
                cacheConfig: new CacheConfig(
                    enabled:    config('gmaps.cache.enabled'),
                    ttlSeconds: config('gmaps.cache.ttl.nearby_search'),
                    prefix:     config('gmaps.cache.prefix'),
                ),
            );
        });

        $this->app->singleton('gmaps', function () {
            return new GmapsManager(
                nearbySearchUseCase: $this->app->make(NearbySearchUseCase::class),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/gmaps.php' => config_path('gmaps.php'),
        ], 'gmaps-config');
    }
}
