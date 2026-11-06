<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    /**
     * Farmers can view their own payouts list.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasActiveFarmerCapability($user);
    }

    /**
     * Only the payout's farmer or an admin can view it.
     */
    public function view(User $user, Payout $payout): bool
    {
        return $user->is_admin || $payout->farmer_id === $user->id;
    }

    /**
     * Admin-only: create a payout record.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: update a payout status.
     */
    public function update(User $user, Payout $payout): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view all payout history.
     */
    public function viewAll(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Check whether the given user has an active farmer capability.
     */
    private function hasActiveFarmerCapability(User $user): bool
    {
        return $user->capabilities()
            ->where('capability_type', 'farmer')
            ->where('status', 'active')
            ->exists();
    }
}
