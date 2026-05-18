<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'status',
        'total_amount',
        'table_session_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function ingredientUsages()
    {
        return $this->hasMany(IngredientUsage::class);
    }
    public function tableSession()
    {
        return $this->belongsTo(\App\Models\TableSession::class, 'table_session_id');
    }
}