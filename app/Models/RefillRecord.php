<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefillRecord extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'menu_item_id',
        'ingredient_id',
        'recorded_by',
        'quantity',
        'unit',
        'refill_number',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'refill_number' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}