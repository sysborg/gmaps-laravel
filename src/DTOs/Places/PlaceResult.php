<?php

namespace Sysborg\GmapsLaravel\DTOs\Places;

use Sysborg\GmapsLaravel\DTOs\Coordinate;

readonly class PlaceResult
{
    public function __construct(
        public string     $placeId,
        public string     $name,
        public string     $vicinity,
        public Coordinate $location,
        public array      $types,
        public ?float     $rating,
        public ?int       $userRatingsTotal,
        public bool       $openNow,
        public ?string    $icon,
        public ?string    $businessStatus,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'place_id'           => $this->placeId,
            'name'               => $this->name,
            'vicinity'           => $this->vicinity,
            'location'           => $this->location->toArray(),
            'types'              => $this->types,
            'rating'             => $this->rating,
            'user_ratings_total' => $this->userRatingsTotal,
            'open_now'           => $this->openNow,
            'icon'               => $this->icon,
            'business_status'    => $this->businessStatus,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromGoogleResponse(array $data): self
    {
        $geometry = $data['geometry']['location'] ?? [];

        return new self(
            placeId:          $data['place_id'] ?? '',
            name:             $data['name'] ?? '',
            vicinity:         $data['vicinity'] ?? '',
            location:         new Coordinate(
                latitude:  (float) ($geometry['lat'] ?? 0),
                longitude: (float) ($geometry['lng'] ?? 0),
            ),
            types:            $data['types'] ?? [],
            rating:           isset($data['rating']) ? (float) $data['rating'] : null,
            userRatingsTotal: isset($data['user_ratings_total']) ? (int) $data['user_ratings_total'] : null,
            openNow:          (bool) ($data['opening_hours']['open_now'] ?? false),
            icon:             $data['icon'] ?? null,
            businessStatus:   $data['business_status'] ?? null,
        );
    }
}
