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
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'decimal:2',
        'flavor_tags' => 'array',
    ];

    protected $appends = [
        'image_url',
        'max_order_quantity',
        'stock_label',
    ];

    protected static function booted()
    {
        static::creating(function ($menuItem) {
            // New menu items should be unavailable first
            // until ingredients are linked and stock is enough.
            $menuItem->is_available = false;
        });
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_item_ingredients')
            ->withPivot('quantity_required')
            ->withTimestamps();
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
        $ingredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        // No linked ingredients = cannot order
        if ($ingredients->isEmpty()) {
            return 0;
        }

        $possibleQuantities = [];

        foreach ($ingredients as $ingredient) {
            $requiredForOneOrder = (float) ($ingredient->pivot->quantity_required ?? 0);

            if ($requiredForOneOrder <= 0) {
                return 0;
            }

            /*
             * Use current_stock because this is what your admin inventory page displays.
             * This makes the customer ordering limit match what admin sees.
             */
            $availableStock = (float) ($ingredient->current_stock ?? 0);

            $possibleQuantities[] = (int) floor($availableStock / $requiredForOneOrder);
        }

        if (empty($possibleQuantities)) {
            return 0;
        }

        return max(0, min($possibleQuantities));
    }

    public function getStockLabelAttribute(): string
    {
        $maxOrderQuantity = $this->max_order_quantity;

        if ($maxOrderQuantity <= 0) {
            return 'Out of stock';
        }

        if ($maxOrderQuantity > 5) {
            return 'Available';
        }

        if ($maxOrderQuantity === 1) {
            return 'Only 1 order left';
        }

        return "Only {$maxOrderQuantity} orders left";
    }

    public function refreshAvailability(): void
    {
        $this->forceFill([
            'is_available' => $this->computeAvailability(),
        ])->saveQuietly();
    }

    public static function refreshAllAvailability(): void
    {
        self::query()->chunk(100, function ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menuItem->refreshAvailability();
            }
        });
    }

    public function computeAvailability(): bool
    {
        return $this->max_order_quantity > 0;
    }
}