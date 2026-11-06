<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Anyone can browse/view active listings (public routes — no policy needed).
     * This method covers the farmer's own listing list.
     */
    /**
     * Authenticated users can view their own listings.
     */
    public function viewOwn(User $user): bool
    {
        return true;
    }

    /**
     * Admin-only: Browse and moderate all listings across system.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Users with active farmer capability or admins can create listings.
     */
    public function create(User $user): bool
    {
        return $this->hasActiveFarmerCapability($user);
    }

    /**
     * Only the listing owner or admin can update.
     */
    public function update(User $user, Listing $listing): bool
    {
        return $user->is_admin || ($this->hasActiveFarmerCapability($user) && $listing->farmer_id === $user->id);
    }

    /**
     * Only the listing owner or admin can delete.
     */
    public function delete(User $user, Listing $listing): bool
    {
        return $user->is_admin || ($this->hasActiveFarmerCapability($user) && $listing->farmer_id === $user->id);
    }

    /**
     * Check whether the given user has an active farmer capability or is an admin.
     */
    private function hasActiveFarmerCapability(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->account_status && $user->account_status !== 'active') {
            return false;
        }

        return $user->capabilities()
            ->where('capability_type', 'farmer')
            ->where('status', 'active')
            ->exists() || $user->isFarmer();
    }
}
