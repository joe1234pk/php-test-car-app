<?php

namespace App\Repositories;

use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class QuoteRepository
{
    /**
     * @param array<int, array<string, mixed>> $quotes
     */
    public function replaceQuotesForCar(int $carId, array $quotes): int
    {
        Quote::query()->where('car_id', $carId)->delete();

        $inserted = 0;
        foreach ($quotes as $quote) {
            $price = $quote['price'] ?? null;
            $repairer = $quote['repairer'] ?? null;
            $overviewOfWork = $quote['overviewOfWork'] ?? null;

            if (!is_numeric($price) || !is_string($repairer) || !is_string($overviewOfWork)) {
                throw new UnprocessableEntityHttpException('Quote payload missing required fields.');
            }

            Quote::query()->create([
                'car_id' => $carId,
                'price' => (float) $price,
                'repairer' => $repairer,
                'overview_of_work' => $overviewOfWork,
                'fetched_at' => Carbon::now(),
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return Collection<int, object>
     */
    public function quotesByCarId(int $carId): Collection
    {
        return Quote::query()
            ->where('car_id', $carId)
            ->orderBy('price_in_cent')
            ->get();
    }
}
