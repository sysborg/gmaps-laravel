<?php

namespace Sysborg\GmapsLaravel\Tests\Feature\Places;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Sysborg\GmapsLaravel\DTOs\Coordinate;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchRequest;
use Sysborg\GmapsLaravel\DTOs\Places\NearbySearchResponse;
use Sysborg\GmapsLaravel\Exceptions\GoogleApiException;
use Sysborg\GmapsLaravel\Facades\GMap;
use Sysborg\GmapsLaravel\GmapsServiceProvider;
use Sysborg\GmapsLaravel\UseCases\Places\NearbySearchUseCase;

class NearbySearchTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [GmapsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('gmaps.api_key', 'test-api-key');
        $app['config']->set('gmaps.cache.enabled', false);
        $app['config']->set('gmaps.http.retry.times', 1);
    }

    private function fixtureJson(string $name): array
    {
        $path = __DIR__ . '/../../Fixtures/' . $name;
        return json_decode(file_get_contents($path), true);
    }

    public function test_nearby_search_returns_typed_response(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response($this->fixtureJson('nearby_search_response.json'), 200),
        ]);

        $useCase  = $this->app->make(NearbySearchUseCase::class);
        $request  = new NearbySearchRequest(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     1500,
        );

        $response = $useCase->execute($request);

        $this->assertInstanceOf(NearbySearchResponse::class, $response);
        $this->assertSame('OK', $response->status);
        $this->assertCount(2, $response->places);
        $this->assertSame('Google Australia', $response->places[0]->name);
        $this->assertTrue($response->hasNextPage());
    }

    public function test_nearby_search_via_facade(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response($this->fixtureJson('nearby_search_response.json'), 200),
        ]);

        $response = GMap::nearbySearch(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     1500,
            type:       'restaurant',
        );

        $this->assertInstanceOf(NearbySearchResponse::class, $response);
        $this->assertCount(2, $response->places);
    }

    public function test_api_key_is_included_in_request(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response($this->fixtureJson('nearby_search_response.json'), 200),
        ]);

        $useCase = $this->app->make(NearbySearchUseCase::class);
        $useCase->execute(new NearbySearchRequest(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     500,
        ));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'key=test-api-key');
        });
    }

    public function test_throws_exception_on_google_api_error_status(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response([
                'status'        => 'REQUEST_DENIED',
                'error_message' => 'The provided API key is invalid.',
            ], 200),
        ]);

        $this->expectException(GoogleApiException::class);
        $this->expectExceptionMessage('The provided API key is invalid.');

        $useCase = $this->app->make(NearbySearchUseCase::class);
        $useCase->execute(new NearbySearchRequest(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     500,
        ));
    }

    public function test_zero_results_returns_empty_response(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response([
                'status'  => 'ZERO_RESULTS',
                'results' => [],
            ], 200),
        ]);

        $useCase  = $this->app->make(NearbySearchUseCase::class);
        $response = $useCase->execute(new NearbySearchRequest(
            coordinate: new Coordinate(0.0, 0.0),
            radius:     100,
        ));

        $this->assertTrue($response->isEmpty());
        $this->assertSame('ZERO_RESULTS', $response->status);
    }

    public function test_response_place_has_correct_coordinates(): void
    {
        Http::fake([
            '*/nearbysearch/json*' => Http::response($this->fixtureJson('nearby_search_response.json'), 200),
        ]);

        $useCase  = $this->app->make(NearbySearchUseCase::class);
        $response = $useCase->execute(new NearbySearchRequest(
            coordinate: new Coordinate(-33.8688, 151.2093),
            radius:     1500,
        ));

        $first = $response->places[0];
        $this->assertSame(-33.866489, $first->location->latitude);
        $this->assertSame(151.1958561, $first->location->longitude);
    }
}
