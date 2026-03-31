<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncRun extends Model
{
    protected $table = 'sync_run';

    protected $fillable = [
        'run_type',
        'status',
        'source_endpoint',
        'records_received',
        'records_inserted',
        'records_updated',
        'error_message',
        'started_at',
        'finished_at',
    ];
}
