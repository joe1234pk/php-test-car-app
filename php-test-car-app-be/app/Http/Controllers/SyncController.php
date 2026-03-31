<?php

namespace App\Http\Controllers;

use App\Services\SyncService;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    private SyncService $syncService;

    public function __construct()
    {
        $this->syncService = new SyncService();
    }

    public function syncCars(): JsonResponse
    {
        $result = $this->syncService->syncCars();

        return response()->json(
            [
                'message' => 'Cars synced successfully.',
                'data' => $result,
            ],
            200
        );
    }

    public function syncQuotesForAllCars(): JsonResponse
    {
        $result = $this->syncService->syncQuotesForAllCars();

        return response()->json(
            [
                'message' => 'Quotes synced successfully for all cars.',
                'data' => $result,
            ],
            200
        );
    }

    public function syncQuotesForCar(int $carId): JsonResponse
    {
        $result = $this->syncService->syncQuotesForCar($carId);

        return response()->json(
            [
                'message' => 'Quotes synced successfully for selected car.',
                'data' => $result,
            ],
            200
        );
    }
}
