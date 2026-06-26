<?php

namespace App\Models;

use Carbon\Carbon;
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
        if (array_key_exists('sold_today', $this->attributes)) {
            return (int) ($this->attributes['sold_today'] ?? 0);
        }

        $selectedDate = now('Asia/Manila')->toDateString();

        $startOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->startOfDay()
            ->timezone('UTC');

        $endOfDayUtc = Carbon::parse($selectedDate, 'Asia/Manila')
            ->endOfDay()
            ->timezone('UTC');

        return (int) $this->orderItems()
            ->whereHas('order', function ($query) use ($startOfDayUtc, $endOfDayUtc) {
                $query->whereBetween('created_at', [$startOfDayUtc, $endOfDayUtc]);
            })
            ->sum('quantity');
    }

    public function getRemainingTodayAttribute(): ?int
    {
        if ($this->inventory_type === 'custom') {
            return null;
        }

        if (!in_array($this->inventory_type, ['per_order', 'per_head'], true)) {
            return null;
        }

        if ($this->daily_limit === null) {
            return null;
        }

        return max(0, (int) $this->daily_limit - (int) $this->sold_today);
    }

    public function getDailyInventoryLabelAttribute(): string
    {
        if (!$this->inventory_type) {
            return 'Inventory type not set';
        }

        if ($this->inventory_type === 'custom') {
            return 'Staff confirms';
        }

        if (!in_array($this->inventory_type, ['per_order', 'per_head'], true)) {
            return 'Inventory type not set';
        }

        if ($this->daily_limit === null) {
            return 'Daily limit not set';
        }

        $unit = $this->inventory_type === 'per_head' ? 'heads' : 'orders';
        $remaining = (int) $this->remaining_today;

        if ($remaining <= 0) {
            return 'Sold out today';
        }

        return "{$remaining} {$unit} left today";
    }

    public function getMaxOrderQuantityAttribute(): int
    {
        if ($this->inventory_type === 'custom') {
            return 99;
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'], true)) {
            if ($this->daily_limit === null) {
                return 0;
            }

            return max(0, (int) $this->remaining_today);
        }

        return 0;
    }

    public function getStockLabelAttribute(): string
    {
        if (!$this->inventory_type) {
            return 'Inventory setup required';
        }

        if ($this->inventory_type === 'custom') {
            return 'Staff confirms';
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'], true)) {
            return $this->daily_inventory_label;
        }

        return 'Inventory setup required';
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
        if ($this->inventory_type === 'custom') {
            return true;
        }

        if (in_array($this->inventory_type, ['per_order', 'per_head'], true)) {
            if ($this->daily_limit === null) {
                return false;
            }

            return (int) $this->remaining_today > 0;
        }

        return false;
    }
}