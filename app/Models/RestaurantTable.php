<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $fillable = [
        'table_number',
        'capacity',
        'status',
        'current_guest_count',
        'current_order_id',
        'current_reservation_id',
        'occupied_at',
        'notes',
    ];

    protected $casts = [
        'occupied_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'current_reservation_id');
    }
}