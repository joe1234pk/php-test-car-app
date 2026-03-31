<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $table = 'car';

    protected $fillable = [
        'license_plate',
        'license_state',
        'vin',
        'manufacture_year',
        'colour',
        'make',
        'model',
        'last_synced_at',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'car_id');
    }
}
