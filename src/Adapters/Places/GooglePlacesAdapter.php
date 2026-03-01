<?php

namespace Sysborg\GmapsLaravel\Adapters\Places;

use Sysborg\GmapsLaravel\Contracts\Http\HttpClientPort;
use Sysborg\GmapsLaravel\Contracts\Places\PlacesPort;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;
use Sysborg\GmapsLaravel\DTOs\Places\PlaceResult;
use Sysborg\GmapsLaravel\Exceptions\GoogleApiException;

class GooglePlacesAdapter implements PlacesPort
{
    private const ALLOWED_STATUSES = ['OK', 'ZERO_RESULTS'];

    public function __construct(
        private readonly HttpClientPort $http,
        private readonly string         $apiKey,
        private readonly string         $baseUrl,
    ) {}

    public function nearbySearch(NearbySearchRequest $request): NearbySearchResponse
    {
        $query = array_merge($request->toQueryParams(), ['key' => $this->apiKey]);

        $data = $this->http->get("{$this->baseUrl}/nearbysearch/json", $query);

        $status = $data['status'] ?? 'UNKNOWN_ERROR';

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $message = $data['error_message'] ?? "Google Places API error: {$status}";
            throw new GoogleApiException($message);
        }

        $places = array_map(
            static fn (array $item) => PlaceResult::fromGoogleResponse($item),
            $data['results'] ?? [],
        );

        return new NearbySearchResponse(
            places:        $places,
            status:        $status,
            nextPageToken: $data['next_page_token'] ?? null,
        );
    }
}
