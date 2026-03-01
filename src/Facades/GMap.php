<?php

namespace Sysborg\GmapsLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Sysborg\GmapsLaravel\DTOs\Coordinate;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;

/**
 * @method static NearbySearchResponse nearbySearch(Coordinate $coordinate, int $radius, ?string $type = null, ?string $keyword = null, string $language = 'en')
 *
 * @see \Sysborg\GmapsLaravel\GmapsManager
 */
class GMap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gmaps';
    }
}
