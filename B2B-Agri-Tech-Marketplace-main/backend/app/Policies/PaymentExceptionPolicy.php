<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\PaymentException;
use App\Models\User;

class PaymentExceptionPolicy
{
    /**
     * Any authenticated user can raise an exception (further participant
     * validation is handled in the controller against the specific order).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Owner or admin can view a payment exception.
     */
    public function view(User $user, PaymentException $exception): bool
    {
        return $user->is_admin || $exception->raised_by === $user->id;
    }

    /**
     * Admin-only: list all payment exceptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: move an exception to investigating.
     */
    public function investigate(User $user, PaymentException $exception): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: resolve an exception.
     */
    public function resolve(User $user, PaymentException $exception): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: reject an exception.
     */
    public function reject(User $user, PaymentException $exception): bool
    {
        return $user->is_admin;
    }

    /**
     * Check whether the given user is a participant in the order
     * (either the buyer or a farmer assigned to a fulfillment).
     */
    public function isOrderParticipant(User $user, Order $order): bool
    {
        if ($order->buyer_id === $user->id) {
            return true;
        }

        return $order->fulfillments()
            ->where('farmer_id', $user->id)
            ->exists();
    }
}
