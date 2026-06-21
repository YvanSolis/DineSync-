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
        'sold_today',
        'remaining_today',
        'daily_inventory_label',
    ];

    protected static function booted()
    {
        static::creating(function ($menuItem) {
            if (!$menuItem->inventory_type) {
                $menuItem->inventory_type = $menuItem->category === 'Chef Oppa Special'
                    ? 'custom'
                    : 'per_order';
            }

            if ($menuItem->inventory_type === 'custom' || $menuItem->category === 'Chef Oppa Special') {
                $menuItem->is_available = true;
                return;
            }

            $menuItem->is_available = true;
        });
    }

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

    public function getSoldTodayAttribute(): int
    {
        return (int) $this->orderItems()
            ->whereDate('created_at', now()->toDateString())
            ->sum('quantity');
    }

    public function getRemainingTodayAttribute(): ?int
    {
        if ($this->inventory_type === 'custom') {
            return null;
        }

        if ($this->daily_limit === null) {
            return null;
        }

        return max(0, (int) $this->daily_limit - (int) $this->sold_today);
    }

    public function getDailyInventoryLabelAttribute(): string
    {
        if ($this->inventory_type === 'custom') {
            return 'Staff confirms';
        }

        if ($this->daily_limit === null) {
            return 'No daily limit set';
        }

        $unit = $this->inventory_type === 'per_head' ? 'heads' : 'orders';

        return "{$this->remaining_today} {$unit} left today";
    }

    public function getMaxOrderQuantityAttribute(): int
    {
        if ($this->inventory_type === 'custom' || $this->category === 'Chef Oppa Special') {
            return 99;
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'])) {
            if ($this->daily_limit === null) {
                return 99;
            }

            return max(0, (int) $this->remaining_today);
        }

        $ingredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return 0;
        }

        $possibleQuantities = [];

        foreach ($ingredients as $ingredient) {
            $requiredForOneOrder = (float) ($ingredient->pivot->quantity_required ?? 0);

            if ($requiredForOneOrder <= 0) {
                return 0;
            }

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
        if ($this->inventory_type === 'custom') {
            return 'Staff confirms';
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'])) {
            return $this->daily_inventory_label;
        }

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
        if ($this->inventory_type === 'custom' || $this->category === 'Chef Oppa Special') {
            return true;
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'])) {
            if ($this->daily_limit === null) {
                return true;
            }

            return $this->remaining_today > 0;
        }

        return $this->max_order_quantity > 0;
    }
}