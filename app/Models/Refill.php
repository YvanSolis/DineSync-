<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refill extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'table_number',
        'status',
        'notes',
        'requested_at',
        'preparing_at',
        'ready_at',
        'served_at',
        'cancelled_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'served_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function items()
    {
        return $this->hasMany(RefillItem::class);
    }
}