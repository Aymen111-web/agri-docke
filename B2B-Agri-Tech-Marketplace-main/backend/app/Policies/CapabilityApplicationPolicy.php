<?php

namespace App\Policies;

use App\Models\CapabilityApplication;
use App\Models\User;

class CapabilityApplicationPolicy
{
    /**
     * Any authenticated user can submit a capability application.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Owner or admin can view a capability application.
     */
    public function view(User $user, CapabilityApplication $application): bool
    {
        return $user->is_admin || $application->user_id === $user->id;
    }

    /**
     * Admin-only: list all capability applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: approve a capability application.
     */
    public function approve(User $user, CapabilityApplication $application): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: reject a capability application.
     */
    public function reject(User $user, CapabilityApplication $application): bool
    {
        return $user->is_admin;
    }
}
