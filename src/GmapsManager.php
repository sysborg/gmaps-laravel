<?php

namespace Sysborg\GmapsLaravel;

use Sysborg\GmapsLaravel\DTOs\Coordinate;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;

/**
 * Facade accessor — provides a clean API surface for the GMap facade.
 */
class GmapsManager
{
    public function __construct(private readonly NearbySearchUseCase $nearbySearchUseCase) {}

    public function nearbySearch(
        Coordinate $coordinate,
        int        $radius,
        ?string    $type     = null,
        ?string    $keyword  = null,
        string     $language = 'en',
        ?string    $pageToken = null,
    ): NearbySearchResponse {
        return $this->nearbySearchUseCase->execute(new NearbySearchRequest(
            coordinate: $coordinate,
            radius:     $radius,
            type:       $type,
            keyword:    $keyword,
            language:   $language,
            pageToken:  $pageToken,
        ));
    }
}
