<?php

namespace App\Policies;

use App\Models\OrderFulfillment;
use App\Models\User;

class OrderFulfillmentPolicy
{
    /**
     * Only users with active farmer capability can list their fulfillments.
     */
    /**
     * Authenticated users can list their own fulfillments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only the assigned farmer or admin can view a fulfillment.
     */
    public function view(User $user, OrderFulfillment $fulfillment): bool
    {
        return $user->is_admin || (int) $fulfillment->farmer_id === (int) $user->id;
    }

    /**
     * Only the assigned farmer or admin can accept a fulfillment.
     */
    public function accept(User $user, OrderFulfillment $fulfillment): bool
    {
        return $user->is_admin || (int) $fulfillment->farmer_id === (int) $user->id;
    }

    /**
     * Only the assigned farmer or admin can reject a fulfillment.
     */
    public function reject(User $user, OrderFulfillment $fulfillment): bool
    {
        return $user->is_admin || (int) $fulfillment->farmer_id === (int) $user->id;
    }

    /**
     * Only the assigned farmer or admin can complete a fulfillment.
     */
    public function complete(User $user, OrderFulfillment $fulfillment): bool
    {
        return $user->is_admin || (int) $fulfillment->farmer_id === (int) $user->id;
    }

    /**
     * Only the order's buyer can confirm received for a fulfillment.
     */
    public function confirmReceived(User $user, OrderFulfillment $fulfillment): bool
    {
        return $fulfillment->order
            && (int) $fulfillment->order->buyer_id === (int) $user->id;
    }

    /**
     * Check whether the given user has an active farmer capability.
     */
    private function hasActiveFarmerCapability(User $user): bool
    {
        return $user->account_status === 'active'
            && $user->capabilities()
                ->where('capability_type', 'farmer')
                ->where('status', 'active')
                ->exists();
    }
}
