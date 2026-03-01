<?php

namespace Sysborg\GmapsLaravel\DTOs\Places;

use Sysborg\GmapsLaravel\DTOs\Coordinate;

readonly class NearbySearchRequest
{
    public function __construct(
        public Coordinate $coordinate,
        public int        $radius,
        public ?string    $type      = null,
        public ?string    $keyword   = null,
        public string     $language  = 'en',
        public ?string    $pageToken = null,
    ) {}

    public function cacheKey(): string
    {
        $params = [
            'lat'       => $this->coordinate->latitude,
            'lng'       => $this->coordinate->longitude,
            'radius'    => $this->radius,
            'type'      => $this->type,
            'keyword'   => $this->keyword,
            'language'  => $this->language,
            'pageToken' => $this->pageToken,
        ];
        ksort($params);

        return 'gmaps:nearby_search:' . hash('sha256', json_encode($params));
    }

    /** @return array<string, mixed> */
    public function toQueryParams(): array
    {
        $params = [
            'location' => $this->coordinate->toString(),
            'radius'   => $this->radius,
            'language' => $this->language,
        ];

        if ($this->type !== null) {
            $params['type'] = $this->type;
        }

        if ($this->keyword !== null) {
            $params['keyword'] = $this->keyword;
        }

        if ($this->pageToken !== null) {
            $params['pagetoken'] = $this->pageToken;
        }

        return $params;
    }
}
