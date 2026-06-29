<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'total_amount',
        'table_number',
        'table_id',
        'table_session_id',
        'customer_name',
        'notes',
        'remarks',
        'special_instructions',
        'xendit_invoice_id',
        'xendit_external_id',
        'xendit_invoice_url',
        'xendit_expiry_date',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'xendit_expiry_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function ingredientUsages()
    {
        return $this->hasMany(IngredientUsage::class, 'order_id');
    }

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class, 'table_session_id');
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}