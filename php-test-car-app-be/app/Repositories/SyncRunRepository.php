<?php

namespace App\Repositories;

use App\Models\SyncRun;
use Carbon\Carbon;

class SyncRunRepository
{
    public function createStarted(string $runType, string $sourceEndpoint): int
    {
        $syncRun = SyncRun::query()->create([
            'run_type' => $runType,
            'status' => 'started',
            'source_endpoint' => $sourceEndpoint,
            'started_at' => Carbon::now(),
        ]);

        return (int) $syncRun->id;
    }

    public function finish(
        int $runId,
        string $status,
        int $received,
        int $inserted,
        int $updated,
        ?string $errorMessage
    ): void {
        SyncRun::query()->whereKey($runId)->update([
            'status' => $status,
            'records_received' => $received,
            'records_inserted' => $inserted,
            'records_updated' => $updated,
            'error_message' => $errorMessage,
            'finished_at' => Carbon::now(),
        ]);
    }
}
