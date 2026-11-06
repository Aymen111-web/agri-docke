<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'second_name',
        'phone',
        'phone_verified_at',
        'password',
        'is_admin',
        'account_status',
        'chapa_subaccount_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'profile_photo_path',
        'company_name',
        'tin_number',
        'region',
        'delivery_hub',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class, 'phone', 'phone');
    }

    public function capabilityApplications()
    {
        return $this->hasMany(CapabilityApplication::class);
    }

    public function capabilities()
    {
        return $this->hasMany(UserCapability::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'farmer_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'buyer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function orderFulfillments()
    {
        return $this->hasMany(OrderFulfillment::class, 'farmer_id');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'farmer_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function isFarmer(): bool
    {
        return $this->capabilities()
            ->where('capability_type', 'farmer')
            ->where('status', 'active')
            ->exists();
    }

    public function hasPaymentDestination(): bool
    {
        return ! empty($this->chapa_subaccount_id) && ! empty($this->account_number);
    }
}
