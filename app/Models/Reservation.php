<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'created_by_role',

        'customer_name',
        'customer_email',
        'customer_phone',
        'reservation_date',
        'reservation_time',
        'guest_count',

        'table_number',
        'arrived_at',
        'seated_at',

        'reservation_fee_amount',
        'reservation_fee_billing_type',
        'reservation_fee_added_to_bill',
        'reservation_fee_added_at',
        'reservation_fee_order_id',

        'payment_method',
        'payment_reference',
        'payment_proof',
        'payment_status',

        'xendit_invoice_id',
        'xendit_external_id',
        'xendit_invoice_url',
        'xendit_expiry_date',
        'paid_at',

        'notes',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'arrived_at' => 'datetime',
        'seated_at' => 'datetime',
        'reservation_fee_added_to_bill' => 'boolean',
        'reservation_fee_added_at' => 'datetime',
        'xendit_expiry_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}