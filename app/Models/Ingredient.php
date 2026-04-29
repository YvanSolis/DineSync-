<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'current_stock',
        'unit',
        'threshold',
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_ingredients')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }
}