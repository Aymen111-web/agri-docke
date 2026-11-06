<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT         = 'pending_payment';
    public const STATUS_PENDING_FARMER_APPROVAL  = 'pending_farmer_approval';
    public const STATUS_AWAITING_BUYER_PAYMENT   = 'awaiting_buyer_payment';
    public const STATUS_PAID_IN_ESCROW           = 'paid_in_escrow';
    public const STATUS_DISPATCHED               = 'dispatched';
    public const STATUS_IN_TRANSIT               = 'in_transit';
    public const STATUS_BUYER_RECEIVED           = 'buyer_received';
    public const STATUS_PROCESSING               = 'processing';
    public const STATUS_PARTIALLY_FULFILLED      = 'partially_fulfilled';
    public const STATUS_COMPLETED                = 'completed';
    public const STATUS_CANCELLED                = 'cancelled';
    public const STATUS_REJECTED                 = 'rejected';

    protected $fillable = [
        'order_number',
        'buyer_id',
        'status',
        'payment_status',
        'delivery_status',
        'inspection_status',
        'payout_status',
        'delivery_pin',
        'delivery_pin_verified_at',
        'reservation_expires_at',
        'produce_amount',
        'platform_fee',
        'fee_rate',
        'total_amount',
        'currency',
        'placed_at',
    ];

    protected $casts = [
        'buyer_id' => 'integer',
        'produce_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'fee_rate' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'placed_at' => 'datetime',
        'reservation_expires_at' => 'datetime',
        'delivery_pin_verified_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function fulfillments()
    {
        return $this->hasMany(OrderFulfillment::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentExceptions()
    {
        return $this->hasMany(PaymentException::class);
    }
}
