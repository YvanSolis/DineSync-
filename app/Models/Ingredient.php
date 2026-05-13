<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'current_stock',
        'unit',
        'threshold',
    ];

    protected $appends = [
        'total_stock',
        'stock_value',
        'nearest_expiry_date',
        'stock_status',
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_ingredients')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function usableBatches()
    {
        return $this->hasMany(InventoryBatch::class)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }

    public function expiredBatches()
    {
        return $this->hasMany(InventoryBatch::class)
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', now()->toDateString());
    }

    public function activeBatches()
    {
        return $this->usableBatches();
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getTotalStockAttribute()
    {
        return (float) $this->usableBatches()->sum('quantity_remaining');
    }

    public function getStockValueAttribute()
    {
        return (float) $this->usableBatches()
            ->selectRaw('COALESCE(SUM(quantity_remaining * unit_cost), 0) as total_value')
            ->value('total_value');
    }

    public function getNearestExpiryDateAttribute()
    {
        $batch = $this->usableBatches()
            ->orderBy('expiry_date')
            ->first();

        return $batch ? $batch->expiry_date : null;
    }

    public function getStockStatusAttribute()
    {
        $usableStock = (float) $this->total_stock;
        $threshold = (float) $this->threshold;

        if ($usableStock <= 0) {
            return 'out_of_stock';
        }

        if ($threshold > 0 && $usableStock < $threshold) {
            return 'low_stock';
        }

        if ($threshold > 0 && $usableStock == $threshold) {
            return 'reorder_soon';
        }

        $nearestExpiry = $this->nearest_expiry_date;

        if ($nearestExpiry && now()->diffInDays($nearestExpiry, false) <= 3) {
            return 'near_expiry';
        }

        return 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh linked menu item availability
    |--------------------------------------------------------------------------
    | After this ingredient changes, refresh all menu items.
    | Rule:
    | - No linked ingredients = unavailable
    | - Has linked ingredients but one ingredient stock is not enough = unavailable
    | - Has linked ingredients and all stocks are enough = available
    */
    public function updateLinkedMenuAvailability(): void
    {
        MenuItem::refreshAllAvailability();
    }

    public static function refreshAllMenuAvailability(): void
    {
        MenuItem::refreshAllAvailability();
    }
}