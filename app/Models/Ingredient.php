<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        if (! Schema::hasTable('inventory_batches')) {
            return (float) ($this->current_stock ?? 0);
        }

        return (float) $this->usableBatches()->sum('quantity_remaining');
    }

    public function getStockValueAttribute()
    {
        if (! Schema::hasTable('inventory_batches')) {
            return 0;
        }

        return (float) $this->usableBatches()
            ->selectRaw('COALESCE(SUM(quantity_remaining * unit_cost), 0) as total_value')
            ->value('total_value');
    }

    public function getNearestExpiryDateAttribute()
    {
        if (! Schema::hasTable('inventory_batches')) {
            return null;
        }

        $batch = $this->usableBatches()
            ->orderBy('expiry_date', 'asc')
            ->first();

        return $batch ? $batch->expiry_date : null;
    }

    public function getStockStatusAttribute()
    {
        $usableStock = (float) $this->total_stock;
        $threshold = (float) ($this->threshold ?? 0);

        if ($usableStock <= 0) {
            return 'out_of_stock';
        }

        $nearestExpiry = $this->nearest_expiry_date;

        if ($nearestExpiry) {
            $daysUntilExpiry = now()
                ->startOfDay()
                ->diffInDays(
                    Carbon::parse($nearestExpiry)->startOfDay(),
                    false
                );

            if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 3) {
                return 'near_expiry';
            }
        }

        if ($threshold > 0 && $usableStock < $threshold) {
            return 'low_stock';
        }

        if ($threshold > 0 && $usableStock == $threshold) {
            return 'reorder_soon';
        }

        return 'active';
    }

    public function syncCurrentStockFromBatches(): void
    {
        if (! Schema::hasTable('inventory_batches')) {
            return;
        }

        InventoryBatch::where('ingredient_id', $this->id)
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update([
                'status' => 'expired',
            ]);

        InventoryBatch::where('ingredient_id', $this->id)
            ->where('quantity_remaining', '<=', 0)
            ->update([
                'status' => 'used_up',
            ]);

        $totalUsableStock = InventoryBatch::where('ingredient_id', $this->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->sum('quantity_remaining');

        $this->forceFill([
            'current_stock' => $totalUsableStock,
        ])->saveQuietly();
    }

    public function updateLinkedMenuAvailability(): void
    {
        MenuItem::refreshAllAvailability();
    }

    public static function refreshAllMenuAvailability(): void
    {
        MenuItem::refreshAllAvailability();
    }

    public static function syncAllCurrentStockAndMenuAvailability(): void
    {
        self::query()->chunk(100, function ($ingredients) {
            foreach ($ingredients as $ingredient) {
                $ingredient->syncCurrentStockFromBatches();
            }
        });

        MenuItem::refreshAllAvailability();
    }
}