<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    /**
     * Only users with active buyer capability can view their cart.
     */
    /**
     * Authenticated users can view their own cart (returns empty list if no items).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only the cart item owner or admin can view it.
     */
    public function view(User $user, CartItem $cartItem): bool
    {
        return $user->is_admin || ($this->hasActiveBuyerCapability($user) && $cartItem->buyer_id === $user->id);
    }

    /**
     * Users with active buyer capability or admins can add to cart.
     */
    public function create(User $user): bool
    {
        return $this->hasActiveBuyerCapability($user);
    }

    /**
     * Only the cart item owner or admin can update it.
     */
    public function update(User $user, CartItem $cartItem): bool
    {
        return $user->is_admin || ($this->hasActiveBuyerCapability($user) && $cartItem->buyer_id === $user->id);
    }

    /**
     * Only the cart item owner or admin can delete it.
     */
    public function delete(User $user, CartItem $cartItem): bool
    {
        return $user->is_admin || ($this->hasActiveBuyerCapability($user) && $cartItem->buyer_id === $user->id);
    }

    /**
     * Users with active buyer capability or admins can clear their cart.
     */
    public function clear(User $user): bool
    {
        return $this->hasActiveBuyerCapability($user);
    }

    /**
     * Check whether the given user has an active buyer capability or is an admin.
     */
    private function hasActiveBuyerCapability(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->capabilities()
            ->where('capability_type', 'buyer')
            ->where('status', 'active')
            ->exists();
    }
}
