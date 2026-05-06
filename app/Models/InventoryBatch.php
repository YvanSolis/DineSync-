<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    protected $fillable = [
        'ingredient_id',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
        'received_date',
        'expiry_date',
        'supplier',
        'status',
        'remarks',
    ];

    protected $casts = [
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}