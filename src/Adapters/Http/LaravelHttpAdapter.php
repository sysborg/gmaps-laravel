<?php

namespace Sysborg\GmapsLaravel\Adapters\Http;

use Illuminate\Support\Facades\Http;
use Sysborg\GmapsLaravel\Contracts\Http\HttpClientPort;
use Sysborg\GmapsLaravel\Exceptions\HttpException;

class LaravelHttpAdapter implements HttpClientPort
{
    public function __construct(
        private readonly int $timeout,
        private readonly int $retryTimes,
        private readonly int $retrySleepMs,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $url, array $query = []): array
    {
        $response = Http::timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleepMs)
            ->get($url, $query);

        if ($response->failed()) {
            throw new HttpException(
                "Google Maps API request failed [{$response->status()}]: {$url}",
                $response->status(),
            );
        }

        return $response->json();
    }
}
