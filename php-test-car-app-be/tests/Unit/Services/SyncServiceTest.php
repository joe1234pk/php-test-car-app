<?php

namespace Tests\Unit\Services;

use App\Repositories\CarRepository;
use App\Repositories\QuoteRepository;
use App\Repositories\SyncRunRepository;
use App\Services\DinggoApiClient;
use App\Services\SyncService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class SyncServiceTest extends TestCase
{
    public function test_sync_cars_success_records_counts_and_finishes_success(): void
    {
        $apiClient = Mockery::mock(DinggoApiClient::class);
        $carRepository = Mockery::mock(CarRepository::class);
        $quoteRepository = Mockery::mock(QuoteRepository::class);
        $syncRunRepository = Mockery::mock(SyncRunRepository::class);

        $apiClient
            ->shouldReceive('fetchCars')
            ->once()
            ->andReturn(
                [
                    [
                        'licensePlate' => 'QWE12E',
                        'licenseState' => 'NSW',
                        'vin' => 'VIN-1',
                        'year' => '2002',
                        'colour' => 'Yellow',
                        'make' => 'Mitsubishi',
                        'model' => 'Eclipse',
                    ],
                    [
                        'licensePlate' => 'ASD34D',
                        'licenseState' => 'VIC',
                        'vin' => 'VIN-2',
                        'year' => '2004',
                        'colour' => 'Orange',
                        'make' => 'Nissan',
                        'model' => 'Frontier',
                    ],
                ]
            );

        $syncRunRepository
            ->shouldReceive('createStarted')
            ->once()
            ->with('cars', '/cars')
            ->andReturn(101);

        $carRepository->shouldReceive('upsertCar')->twice()->andReturn(1, 2);

        $syncRunRepository
            ->shouldReceive('finish')
            ->once()
            ->with(101, 'success', 2, 2, 0, null);

        $service = new SyncService($apiClient, $carRepository, $quoteRepository, $syncRunRepository);
        $result = $service->syncCars();

        $this->assertSame(
            ['records_received' => 2, 'records_inserted_or_updated' => 2],
            $result
        );
    }

    public function test_sync_cars_failure_marks_failed_and_rethrows_exception(): void
    {
        $apiClient = Mockery::mock(DinggoApiClient::class);
        $carRepository = Mockery::mock(CarRepository::class);
        $quoteRepository = Mockery::mock(QuoteRepository::class);
        $syncRunRepository = Mockery::mock(SyncRunRepository::class);

        $apiClient
            ->shouldReceive('fetchCars')
            ->once()
            ->andReturn(
                [
                    [
                        'licensePlate' => '',
                        'licenseState' => 'NSW',
                        'vin' => '',
                    ],
                ]
            );

        $syncRunRepository
            ->shouldReceive('createStarted')
            ->once()
            ->with('cars', '/cars')
            ->andReturn(102);

        $carRepository
            ->shouldReceive('upsertCar')
            ->once()
            ->andThrow(new UnprocessableEntityHttpException('Car payload missing required fields.'));

        $syncRunRepository
            ->shouldReceive('finish')
            ->once()
            ->with(102, 'failed', 1, 0, 0, 'Car payload missing required fields.');

        $service = new SyncService($apiClient, $carRepository, $quoteRepository, $syncRunRepository);

        $this->expectException(UnprocessableEntityHttpException::class);
        $service->syncCars();
    }

    public function test_sync_quotes_for_car_uses_flat_args_and_finishes_success(): void
    {
        $apiClient = Mockery::mock(DinggoApiClient::class);
        $carRepository = Mockery::mock(CarRepository::class);
        $quoteRepository = Mockery::mock(QuoteRepository::class);
        $syncRunRepository = Mockery::mock(SyncRunRepository::class);

        $car = (object) [
            'id' => 1,
            'license_plate' => 'QWE12E',
            'license_state' => 'NSW',
        ];

        $syncRunRepository
            ->shouldReceive('createStarted')
            ->once()
            ->with('quotes', '/quotes')
            ->andReturn(201);

        $carRepository
            ->shouldReceive('findCarByIdOrFail')
            ->once()
            ->with(1)
            ->andReturn($car);

        $apiClient
            ->shouldReceive('fetchQuotes')
            ->once()
            ->with('QWE12E', 'NSW')
            ->andReturn(
                [
                    [
                        'price' => 163.58,
                        'repairer' => 'Repairer A',
                        'overviewOfWork' => 'Fix bumper',
                    ],
                    [
                        'price' => 474.08,
                        'repairer' => 'Repairer B',
                        'overviewOfWork' => 'Fix paint',
                    ],
                ]
            );

        $quoteRepository
            ->shouldReceive('replaceQuotesForCar')
            ->once()
            ->with(1, Mockery::type('array'))
            ->andReturn(2);

        $syncRunRepository
            ->shouldReceive('finish')
            ->once()
            ->with(201, 'success', 2, 2, 0, null);

        $service = new SyncService($apiClient, $carRepository, $quoteRepository, $syncRunRepository);
        $result = $service->syncQuotesForCar(1);

        $this->assertSame(['records_received' => 2, 'records_inserted' => 2], $result);
    }

    public function test_sync_quotes_for_car_not_found_marks_failed(): void
    {
        $apiClient = Mockery::mock(DinggoApiClient::class);
        $carRepository = Mockery::mock(CarRepository::class);
        $quoteRepository = Mockery::mock(QuoteRepository::class);
        $syncRunRepository = Mockery::mock(SyncRunRepository::class);

        $syncRunRepository
            ->shouldReceive('createStarted')
            ->once()
            ->with('quotes', '/quotes')
            ->andReturn(202);

        $carRepository
            ->shouldReceive('findCarByIdOrFail')
            ->once()
            ->with(77)
            ->andThrow(new NotFoundHttpException('Car not found.'));

        $syncRunRepository
            ->shouldReceive('finish')
            ->once()
            ->with(202, 'failed', 0, 0, 0, 'Car not found.');

        $service = new SyncService($apiClient, $carRepository, $quoteRepository, $syncRunRepository);

        $this->expectException(NotFoundHttpException::class);
        $service->syncQuotesForCar(77);
    }
}
