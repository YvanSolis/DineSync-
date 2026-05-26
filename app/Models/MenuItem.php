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

        // If old records still use direct online image URL
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        // For uploaded Laravel storage images
        return asset('storage/' . $this->image);
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
        $ingredients = $this->ingredients()->get();

        // No linked ingredients = unavailable
        if ($ingredients->isEmpty()) {
            return false;
        }

        foreach ($ingredients as $ingredient) {
            $requiredForOneOrder = (float) ($ingredient->pivot->quantity_required ?? 0);

            if ($requiredForOneOrder <= 0) {
                return false;
            }

            /*
             * Use current_stock because this is what your inventory page displays.
             * If your inventory is based on batches later, we can change this to total_stock.
             */
            $availableStock = (float) ($ingredient->current_stock ?? 0);

            if ($availableStock < $requiredForOneOrder) {
                return false;
            }
        }

        return true;
    }
}