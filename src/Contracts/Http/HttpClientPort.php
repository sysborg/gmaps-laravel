<?php

namespace Sysborg\GmapsLaravel\Contracts\Http;

interface HttpClientPort
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $url, array $query = []): array;
}
