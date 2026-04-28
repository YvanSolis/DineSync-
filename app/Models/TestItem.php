<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientUsage extends Model
{
    protected $fillable = [
        'ingredient_id',
        'order_id',
        'quantity_used',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}