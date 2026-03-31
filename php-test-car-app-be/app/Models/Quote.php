<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $table = 'quote';

    protected $fillable = [
        'car_id',
        'price',
        'repairer',
        'overview_of_work',
        'fetched_at',
    ];

    protected $hidden = [
        'price_in_cent',
    ];

    protected $appends = [
        'price',
    ];

    public function setPriceAttribute(float|int|string $value): void
    {
        $this->attributes['price_in_cent'] = (int) round(((float) $value) * 100);
    }

    public function getPriceAttribute(): ?float
    {
        if (!isset($this->attributes['price_in_cent'])) {
            return null;
        }

        return round(((int) $this->attributes['price_in_cent']) / 100, 2);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
