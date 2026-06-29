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

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    public function getMaxOrderQuantityAttribute(): int
    {
        if ($this->category === 'Chef Oppa Special' || $this->inventory_type === 'custom') {
            return 99;
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return 0;
        }

        $maxServings = null;

        foreach ($ingredients as $ingredient) {
            $required = (float) ($ingredient->pivot->quantity_required ?? 0);

            if ($required <= 0) {
                return 0;
            }

            $stock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);
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

    public function getStockLabelAttribute(): string
    {
        if ($this->category === 'Chef Oppa Special' || $this->inventory_type === 'custom') {
            return 'Staff confirms availability';
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return 'No ingredients linked';
        }

        foreach ($ingredients as $ingredient) {
            $required = (float) ($ingredient->pivot->quantity_required ?? 0);
            $stock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);

            if ($required <= 0) {
                return 'Invalid ingredient usage';
            }

            if ($stock < $required) {
                return 'Insufficient ingredients';
            }
        }

        $maxOrderQuantity = $this->getMaxOrderQuantityAttribute();

        if ($maxOrderQuantity <= 0) {
            return 'Insufficient ingredients';
        }

        return $maxOrderQuantity . ' orders available based on ingredients';
    }

    public function refreshAvailability(): void
    {
        $this->forceFill([
            'inventory_type' => $this->inventory_type ?: 'per_order',
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

    public function computeAvailability(): bool
    {
        if ($this->category === 'Chef Oppa Special' || $this->inventory_type === 'custom') {
            return true;
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->getRelation('ingredients')
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return false;
        }

        foreach ($ingredients as $ingredient) {
            $required = (float) ($ingredient->pivot->quantity_required ?? 0);
            $stock = (float) ($ingredient->total_stock ?? $ingredient->current_stock ?? 0);

            if ($required <= 0) {
                return false;
            }

            if ($stock < $required) {
                return false;
            }
        }

        return true;
    }
}