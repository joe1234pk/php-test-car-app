<?php

namespace App\Services;

use App\Repositories\CarRepository;
use App\Repositories\QuoteRepository;
use App\Repositories\SyncRunRepository;
use Throwable;

class SyncService
{
    private DinggoApiClient $dinggoApiClient;
    private CarRepository $carRepository;
    private QuoteRepository $quoteRepository;
    private SyncRunRepository $syncRunRepository;

    public function __construct(
        ?DinggoApiClient $dinggoApiClient = null,
        ?CarRepository $carRepository = null,
        ?QuoteRepository $quoteRepository = null,
        ?SyncRunRepository $syncRunRepository = null
    )
    {
        $this->dinggoApiClient = $dinggoApiClient ?? new DinggoApiClient();
        $this->carRepository = $carRepository ?? new CarRepository();
        $this->quoteRepository = $quoteRepository ?? new QuoteRepository();
        $this->syncRunRepository = $syncRunRepository ?? new SyncRunRepository();
    }

    /**
     * @return array<string, int>
     */
    public function syncCars(): array
    {
        $runId = $this->startSyncRun('cars', '/cars');
        $cars = [];
        $insertedOrUpdated = 0;

        try {
            $cars = $this->dinggoApiClient->fetchCars();

            foreach ($cars as $carPayload) {
                $this->carRepository->upsertCar($carPayload);
                $insertedOrUpdated++;
            }

            $this->finishSyncRun($runId, 'success', count($cars), $insertedOrUpdated, 0, null);

            return [
                'records_received' => count($cars),
                'records_inserted_or_updated' => $insertedOrUpdated,
            ];
        } catch (Throwable $exception) {
            $this->finishSyncRun(
                $runId,
                'failed',
                count($cars),
                $insertedOrUpdated,
                0,
                $exception->getMessage()
            );
            throw $exception;
        }
    }

    /**
     * @return array<string, int>
     */
    public function syncQuotesForCar(int $carId): array
    {
        $runId = $this->startSyncRun('quotes', '/quotes');
        $quotes = [];
        $inserted = 0;

        try {
            $car = $this->carRepository->findCarByIdOrFail($carId);

            $quotes = $this->dinggoApiClient->fetchQuotes(
                (string) $car->license_plate,
                (string) $car->license_state
            );
            $inserted = $this->quoteRepository->replaceQuotesForCar($carId, $quotes);

            $this->finishSyncRun($runId, 'success', count($quotes), $inserted, 0, null);

            return [
                'records_received' => count($quotes),
                'records_inserted' => $inserted,
            ];
        } catch (Throwable $exception) {
            $this->finishSyncRun($runId, 'failed', count($quotes), $inserted, 0, $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @return array<string, int>
     */
    public function syncQuotesForAllCars(): array
    {
        $cars = $this->carRepository->allCars();
        $carsProcessed = 0;
        $quotesInserted = 0;

        foreach ($cars as $car) {
            $result = $this->syncQuotesForCar((int) $car->id);
            $carsProcessed++;
            $quotesInserted += (int) ($result['records_inserted'] ?? 0);
        }

        return [
            'cars_processed' => $carsProcessed,
            'quotes_inserted' => $quotesInserted,
        ];
    }

    private function startSyncRun(string $runType, string $sourceEndpoint): int
    {
        return $this->syncRunRepository->createStarted($runType, $sourceEndpoint);
    }

    private function finishSyncRun(
        int $runId,
        string $status,
        int $received,
        int $inserted,
        int $updated,
        ?string $errorMessage
    ): void {
        $this->syncRunRepository->finish(
            $runId,
            $status,
            $received,
            $inserted,
            $updated,
            $errorMessage
        );
    }
}
