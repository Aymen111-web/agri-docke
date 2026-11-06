<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin-only: list all users.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view a specific user's details.
     */
    public function view(User $user, User $target): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view a user's capabilities.
     */
    public function viewCapabilities(User $user, User $target): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: suspend a user.
     */
    public function suspend(User $user, User $target): bool
    {
        return $user->is_admin && $user->id !== $target->id;
    }

    /**
     * Admin-only: activate a user.
     */
    public function activate(User $user, User $target): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view user stats.
     */
    public function viewStats(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Any authenticated user can view their own profile.
     */
    public function viewProfile(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can update their own profile.
     */
    public function updateProfile(User $user): bool
    {
        return true;
    }
}
