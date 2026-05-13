<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'image',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($menuItem) {
            // New menu items are unavailable first until ingredients are linked.
            $menuItem->is_available = false;
        });
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_item_ingredients')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    | This makes $menuItem->is_available always computed live.
    | Rule:
    | - No ingredients = unavailable
    | - Any ingredient stock lower than required = unavailable
    | - All linked ingredients enough = available
    */
    public function getIsAvailableAttribute($value): bool
    {
        return $this->computeAvailability();
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
                $menuItem->forceFill([
                    'is_available' => $menuItem->computeAvailability(),
                ])->saveQuietly();
            }
        });
    }

    public function computeAvailability(): bool
    {
        $ingredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        // No linked ingredients = unavailable
        if ($ingredients->isEmpty()) {
            return false;
        }

        foreach ($ingredients as $ingredient) {
            $requiredForOneOrder = (float) $ingredient->pivot->quantity_required;

            // Use current_stock because this is what your inventory page displays.
            $freshIngredient = Ingredient::find($ingredient->id);

            if (!$freshIngredient) {
                return false;
            }

            $availableStock = (float) $freshIngredient->current_stock;

            // If even one ingredient is not enough, unavailable
            if ($availableStock < $requiredForOneOrder) {
                return false;
            }
        }

        return true;
    }
}