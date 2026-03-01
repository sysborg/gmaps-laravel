<?php

namespace Sysborg\GmapsLaravel\Contracts\Cache;

interface CachePort
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value, int $ttlSeconds): void;

    public function forget(string $key): void;

    public function has(string $key): bool;
}
