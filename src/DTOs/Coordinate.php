<?php

namespace Sysborg\GmapsLaravel\DTOs;

readonly class Coordinate
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public function toString(): string
    {
        return "{$this->latitude},{$this->longitude}";
    }

    /** @return array{lat: float, lng: float} */
    public function toArray(): array
    {
        return ['lat' => $this->latitude, 'lng' => $this->longitude];
    }
}
