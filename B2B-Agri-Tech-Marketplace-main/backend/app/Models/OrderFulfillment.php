<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFulfillment extends Model
{
    protected $fillable = [
        'order_id',
        'farmer_id',
        'status',
        'delivery_status',
        'inspection_status',
        'payout_status',
        'subtotal_amount',
        'produce_amount',
        'platform_fee',
        'farmer_net_payout',
        'accepted_quantity',
        'rejected_quantity',
        'inspection_notes',
        'farmer_notes',
        'accepted_at',
        'rejected_at',
        'inspected_at',
        'completed_at',
    ];

    protected $casts = [
        'order_id'          => 'integer',
        'farmer_id'         => 'integer',
        'subtotal_amount'   => 'decimal:2',
        'produce_amount'    => 'decimal:2',
        'platform_fee'      => 'decimal:2',
        'farmer_net_payout' => 'decimal:2',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'accepted_at'       => 'datetime',
        'rejected_at'       => 'datetime',
        'inspected_at'      => 'datetime',
        'completed_at'      => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }
}
