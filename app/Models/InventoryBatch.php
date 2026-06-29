<?php

namespace App\Models;

use Carbon\Carbon;
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
        'quantity_received' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $appends = [
        'days_until_expiry',
        'expiry_label',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()
            ->startOfDay()
            ->diffInDays(
                Carbon::parse($this->expiry_date)->startOfDay(),
                false
            );
    }

    public function getExpiryLabelAttribute()
    {
        if (! $this->expiry_date) {
            return 'No expiry date';
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return 'Expired';
        }

        if ($days === 0) {
            return 'Expires today';
        }

        if ($days <= 3) {
            return 'Expires in ' . $days . ' day' . ($days === 1 ? '' : 's');
        }

        return 'Active';
    }
}