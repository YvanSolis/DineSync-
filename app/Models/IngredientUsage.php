<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemIngredient extends Model
{
    protected $fillable = [
        'menu_item_id',
        'ingredient_id',
        'quantity_required',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}