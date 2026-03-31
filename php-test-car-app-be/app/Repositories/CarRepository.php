<?php

namespace App\Repositories;

use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CarRepository
{
    public function upsertCar(array $carPayload): int
    {
        $licensePlate = (string) ($carPayload['licensePlate'] ?? '');
        $licenseState = (string) ($carPayload['licenseState'] ?? '');
        $vin = (string) ($carPayload['vin'] ?? '');

        if ($licensePlate === '' || $licenseState === '' || $vin === '') {
            throw new UnprocessableEntityHttpException('Car payload missing required fields.');
        }

        $carData = [
            'license_plate' => $licensePlate,
            'license_state' => $licenseState,
            'vin' => $vin,
            'manufacture_year' => (int) ($carPayload['year'] ?? 0),
            'colour' => (string) ($carPayload['colour'] ?? ''),
            'make' => (string) ($carPayload['make'] ?? ''),
            'model' => (string) ($carPayload['model'] ?? ''),
            'last_synced_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $existingCar = Car::query()
            ->where('license_plate', $licensePlate)
            ->where('license_state', $licenseState)
            ->first();

        if ($existingCar !== null) {
            $existingCar->fill($carData);
            $existingCar->save();

            return (int) $existingCar->id;
        }

        $createdCar = Car::query()->create($carData);
        return (int) $createdCar->id;
    }

    /**
     * @return Collection<int, object>
     */
    public function allCars(): Collection
    {
        return Car::query()->orderBy('id')->get();
    }

    public function findCarById(int $carId): ?object
    {
        return Car::query()->find($carId);
    }

    public function findCarByIdOrFail(int $carId): object
    {
        $car = $this->findCarById($carId);
        if ($car === null) {
            throw new NotFoundHttpException('Car not found.');
        }

        return $car;
    }
}
