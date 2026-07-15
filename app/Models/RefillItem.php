<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'refill_id',
        'ingredient_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function refill()
    {
        return $this->belongsTo(Refill::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}