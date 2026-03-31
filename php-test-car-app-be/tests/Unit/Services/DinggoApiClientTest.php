<?php

namespace Tests\Unit\Services;

use App\Services\DinggoApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class DinggoApiClientTest extends TestCase
{
    public function test_fetch_cars_returns_payload_items_and_sends_credentials(): void
    {
        $history = [];
        $client = $this->buildClient(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(
                        [
                            'success' => 'ok',
                            'cars' => [
                                ['licensePlate' => 'QWE12E'],
                                ['licensePlate' => 'ASD34D'],
                            ],
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ),
            ],
            $history
        );

        $service = new DinggoApiClient($client, 'user@example.com', 'secret-key');
        $cars = $service->fetchCars();

        $this->assertCount(2, $cars);
        $this->assertSame('QWE12E', $cars[0]['licensePlate']);
        $this->assertSame('username=user%40example.com&key=secret-key', (string) $history[0]['request']->getBody());
    }

    public function test_fetch_quotes_returns_payload_items_and_sends_car_fields(): void
    {
        $history = [];
        $client = $this->buildClient(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(
                        [
                            'success' => 'ok',
                            'quotes' => [
                                ['price' => 163.58, 'repairer' => 'A', 'overviewOfWork' => 'x'],
                            ],
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ),
            ],
            $history
        );

        $service = new DinggoApiClient($client, 'user@example.com', 'secret-key');
        $quotes = $service->fetchQuotes('QWE12E', 'NSW');

        $this->assertCount(1, $quotes);
        $this->assertSame(163.58, $quotes[0]['price']);
        $this->assertSame(
            'username=user%40example.com&key=secret-key&licensePlate=QWE12E&licenseState=NSW',
            (string) $history[0]['request']->getBody()
        );
    }

    public function test_fetch_cars_throws_http_exception_when_credentials_missing(): void
    {
        $history = [];
        $client = $this->buildClient([], $history);
        $service = new DinggoApiClient($client, '', '');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Dinggo credentials are missing.');

        $service->fetchCars();
    }

    public function test_fetch_cars_throws_unprocessable_entity_on_api_error(): void
    {
        $history = [];
        $client = $this->buildClient(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(
                        [
                            'success' => 'error',
                            'message' => 'wrong request data',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ),
            ],
            $history
        );

        $service = new DinggoApiClient($client, 'user@example.com', 'secret-key');

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('wrong request data');

        $service->fetchCars();
    }

    public function test_fetch_cars_throws_service_unavailable_on_invalid_json(): void
    {
        $history = [];
        $client = $this->buildClient(
            [
                new Response(200, ['Content-Type' => 'application/json'], 'not-json'),
            ],
            $history
        );

        $service = new DinggoApiClient($client, 'user@example.com', 'secret-key');

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('Invalid JSON response from Dinggo API.');

        $service->fetchCars();
    }

    /**
     * @param array<int, Response> $responses
     * @param array<int, array<string, mixed>> $history
     */
    private function buildClient(array $responses, array &$history): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));

        return new Client(
            [
                'handler' => $handlerStack,
                'base_uri' => 'https://example.test/',
            ]
        );
    }
}
