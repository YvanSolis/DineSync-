<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'reservation_date',
        'reservation_time',
        'guest_count',

        // Service staff fields
        'table_number',
        'arrived_at',
        'seated_at',

        // Payment fields
        'reservation_fee_amount',
        'payment_method',
        'payment_reference',
        'payment_proof',
        'payment_status',

        'notes',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'arrived_at' => 'datetime',
        'seated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}