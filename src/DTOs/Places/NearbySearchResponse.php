<?php

namespace Sysborg\GmapsLaravel\DTOs\Places;

readonly class NearbySearchResponse
{
    /**
     * @param PlaceResult[] $places
     */
    public function __construct(
        public array   $places,
        public string  $status,
        public ?string $nextPageToken = null,
    ) {}

    public function isEmpty(): bool
    {
        return empty($this->places);
    }

    public function hasNextPage(): bool
    {
        return $this->nextPageToken !== null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'status'          => $this->status,
            'next_page_token' => $this->nextPageToken,
            'places'          => array_map(fn (PlaceResult $p) => $p->toArray(), $this->places),
        ];
    }
}
