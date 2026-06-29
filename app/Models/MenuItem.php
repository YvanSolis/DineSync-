<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'image',
        'is_available',
        'flavor_tags',
        'meal_type',
        'inventory_type',
        'daily_limit',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'decimal:2',
        'flavor_tags' => 'array',
        'daily_limit' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'max_order_quantity',
        'stock_label',
    ];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_item_ingredients')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    public function isCustomRequest(): bool
    {
        return $this->category === 'Chef Oppa Special' || $this->inventory_type === 'custom';
    }

    public function getMaxOrderQuantityAttribute(): int
    {
        if ($this->isCustomRequest()) {
            return 1;
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        return $this->computeMaxOrderQuantityFromIngredients($ingredients);
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->isCustomRequest()) {
            return 'Custom request available';
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return $this->is_available
                ? 'Available'
                : 'No ingredients linked';
        }

        foreach ($ingredients as $ingredient) {
            $required = (float) ($ingredient->pivot->quantity_required ?? 0);
            $stock = $this->getIngredientUsableStockValue($ingredient);

            if ($required <= 0) {
                return 'Invalid ingredient usage';
            }

            if ($stock < $required) {
                return 'Unavailable based on ingredient stock.';
            }
        }

        $maxOrderQuantity = $this->computeMaxOrderQuantityFromIngredients($ingredients);

        if ($maxOrderQuantity <= 0) {
            return 'Unavailable based on ingredient stock.';
        }

        return 'Only ' . $maxOrderQuantity . ' order(s) available based on ingredient stock.';
    }

    public function computeAvailability(): bool
    {
        if ($this->isCustomRequest()) {
            return true;
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        /*
        |--------------------------------------------------------------------------
        | No linked ingredients
        |--------------------------------------------------------------------------
        | Do not automatically force all no-ingredient items unavailable.
        | If admin currently enabled it, keep it available.
        */
        if ($ingredients->isEmpty()) {
            return (bool) $this->is_available;
        }

        return $this->computeMaxOrderQuantityFromIngredients($ingredients) > 0;
    }

    public function refreshAvailability(): void
    {
        $this->loadMissing('ingredients');

        if ($this->isCustomRequest()) {
            $this->forceFill([
                'inventory_type' => 'custom',
                'daily_limit' => null,
                'is_available' => true,
            ])->saveQuietly();

            return;
        }

        $this->forceFill([
            'inventory_type' => 'ingredient',
            'daily_limit' => null,
            'is_available' => $this->computeAvailability(),
        ])->saveQuietly();
    }

    public static function refreshAllAvailability(): void
    {
        self::with('ingredients')->chunk(100, function ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menuItem->refreshAvailability();
            }
        });
    }

    private function computeMaxOrderQuantityFromIngredients($ingredients): int
    {
        if ($ingredients->isEmpty()) {
            return 0;
        }

        $maxServings = null;

        foreach ($ingredients as $ingredient) {
            $required = (float) ($ingredient->pivot->quantity_required ?? 0);
            $stock = $this->getIngredientUsableStockValue($ingredient);

            if ($required <= 0) {
                return 0;
            }

            $possibleServings = (int) floor($stock / $required);

            if ($possibleServings <= 0) {
                return 0;
            }

            if ($maxServings === null || $possibleServings < $maxServings) {
                $maxServings = $possibleServings;
            }
        }

        return max(0, (int) ($maxServings ?? 0));
    }

    private function getIngredientUsableStockValue($ingredient): float
    {
        /*
        |--------------------------------------------------------------------------
        | Stock source priority
        |--------------------------------------------------------------------------
        | 1. total_stock from optimized SQL select/subquery
        | 2. current_stock synced from usable batches
        | 3. fallback 0
        */
        if (isset($ingredient->total_stock)) {
            return (float) $ingredient->total_stock;
        }

        if (isset($ingredient->current_stock)) {
            return (float) $ingredient->current_stock;
        }

        return 0;
    }
}