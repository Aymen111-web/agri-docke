<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Only the order's buyer with an active buyer capability can initiate payment.
     */
    public function initiate(User $user, Order $order): bool
    {
        return $user->is_admin || (int) $order->buyer_id === (int) $user->id;
    }

    /**
     * Only the order's buyer or admin can view payment details.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->is_admin || (int) $order->buyer_id === (int) $user->id;
    }
}
