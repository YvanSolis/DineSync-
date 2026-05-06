<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientUsage extends Model
{
    protected $fillable = [
        'ingredient_id',
        'order_id',
        'order_item_id',
        'menu_item_id',
        'quantity_used',
        'unit',
        'remarks',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

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
}