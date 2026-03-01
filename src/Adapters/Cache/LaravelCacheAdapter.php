<?php

namespace Sysborg\GmapsLaravel\Adapters\Cache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Sysborg\GmapsLaravel\Contracts\Cache\CachePort;

class LaravelCacheAdapter implements CachePort
{
    public function __construct(private readonly CacheRepository $cache) {}

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        $this->cache->put($key, $value, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
}
