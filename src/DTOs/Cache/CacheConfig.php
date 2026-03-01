<?php

namespace Sysborg\GmapsLaravel\DTOs\Cache;

readonly class CacheConfig
{
    public function __construct(
        public bool   $enabled,
        public int    $ttlSeconds,
        public string $prefix = 'gmaps',
    ) {}
}
