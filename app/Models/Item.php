<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'current_stock',
        'minimum_stock',
        'stock_tracking_type',
        'weekly_consumption'
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function consumptionRecords(): HasMany
    {
        return $this->hasMany(ConsumptionRecord::class);
    }
} 