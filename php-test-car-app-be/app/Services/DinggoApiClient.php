<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DinggoApiClient
{
    private Client $httpClient;
    private string $username;
    private string $key;

    public function __construct(
        ?Client $httpClient = null,
        ?string $username = null,
        ?string $key = null,
        ?string $baseUrl = null
    )
    {
        $resolvedBaseUrl = $baseUrl ?? (string) env('DINGGO_BASE_URL', '');
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => rtrim($resolvedBaseUrl, '/') . '/',
            'timeout' => 15,
        ]);

        $this->username = $username ?? (string) env('DINGGO_USERNAME', '');
        $this->key = $key ?? (string) env('DINGGO_KEY', '');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchCars(): array
    {
        $response = $this->requestCars();

        return isset($response['cars']) && is_array($response['cars']) ? $response['cars'] : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchQuotes(string $licensePlate, string $licenseState): array
    {
        $response = $this->requestQuotes($licensePlate, $licenseState);

        return isset($response['quotes']) && is_array($response['quotes']) ? $response['quotes'] : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestCars(): array
    {
        return $this->request('cars');
    }

    /**
     * @return array<string, mixed>
     */
    private function requestQuotes(string $licensePlate, string $licenseState): array
    {
        return $this->request('quotes', $licensePlate, $licenseState);
    }

    /**
     * @return array<string, mixed>
     */
    private function request(string $endpoint, ?string $licensePlate = null, ?string $licenseState = null): array
    {
        if ($this->username === '' || $this->key === '') {
            throw new HttpException(500, 'Dinggo credentials are missing.');
        }

        $formData = [
            'username' => $this->username,
            'key' => $this->key,
        ];

        if ($licensePlate !== null) {
            $formData['licensePlate'] = $licensePlate;
        }

        if ($licenseState !== null) {
            $formData['licenseState'] = $licenseState;
        }

        try {
            $response = $this->httpClient->post(
                $endpoint,
                [
                    'form_params' => $formData,
                    'headers' => ['Accept' => 'application/json'],
                ]
            );
        } catch (GuzzleException $exception) {
            throw new ServiceUnavailableHttpException(
                null,
                'Failed to connect to Dinggo API.',
                $exception
            );
        }

        $decodedBody = json_decode((string) $response->getBody(), true);
        if (!is_array($decodedBody)) {
            throw new ServiceUnavailableHttpException(null, 'Invalid JSON response from Dinggo API.');
        }

        if (($decodedBody['success'] ?? 'error') !== 'ok') {
            $message = (string) ($decodedBody['message'] ?? 'Unknown API error');
            throw new UnprocessableEntityHttpException($message);
        }

        return $decodedBody;
    }
}
