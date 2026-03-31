<?php

namespace App\Http\Controllers;

use App\Repositories\CarRepository;
use App\Repositories\QuoteRepository;
use Illuminate\Http\JsonResponse;

class CarController extends Controller
{
    private CarRepository $carRepository;
    private QuoteRepository $quoteRepository;

    public function __construct()
    {
        $this->carRepository = new CarRepository();
        $this->quoteRepository = new QuoteRepository();
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->carRepository->allCars()]);
    }

    public function show(int $carId): JsonResponse
    {
        $car = $this->carRepository->findCarByIdOrFail($carId);

        return response()->json(['data' => $car]);
    }

    public function quotes(int $carId): JsonResponse
    {
        $car = $this->carRepository->findCarByIdOrFail($carId);

        return response()->json(
            [
                'car' => $car,
                'data' => $this->quoteRepository->quotesByCarId($carId),
            ]
        );
    }
}
