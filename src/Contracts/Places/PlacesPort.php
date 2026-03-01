<?php

namespace Sysborg\GmapsLaravel\Contracts\Places;

use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;

interface PlacesPort
{
    public function nearbySearch(NearbySearchRequest $request): NearbySearchResponse;
}
