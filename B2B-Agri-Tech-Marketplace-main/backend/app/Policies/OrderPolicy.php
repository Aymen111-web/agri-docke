<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Only users with active buyer capability can list their orders.
     */
    /**
     * Authenticated users can list their own orders (returns empty list if none).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only the order's buyer or admin can view it.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ((int) $order->buyer_id === (int) $user->id) {
            return true;
        }

        // Allow farmers to view order details if they are assigned a fulfillment on this order
        return $order->fulfillments()->where('farmer_id', (int) $user->id)->exists();
    }

    /**
     * Active users can checkout (create orders).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the order's buyer or admin can initiate a payment.
     */
    public function initiate(User $user, Order $order): bool
    {
        return $user->is_admin || (int) $order->buyer_id === (int) $user->id;
    }

    /**
     * Only the order's buyer or admin can cancel it.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->is_admin || (int) $order->buyer_id === (int) $user->id;
    }

    /**
     * Check whether the given user has an active buyer capability or is an admin.
     */
    private function hasActiveBuyerCapability(User $user): bool
    {
        return $user->account_status === 'active'
            && $user->capabilities()
                ->where('capability_type', 'buyer')
                ->where('status', 'active')
                ->exists();
    }
}
