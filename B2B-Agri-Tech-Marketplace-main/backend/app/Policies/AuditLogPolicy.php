<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Admin-only: list all audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view a single audit log entry.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view audit logs for a specific resource.
     */
    public function viewForResource(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: view audit logs for a specific user.
     */
    public function viewForUser(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Any authenticated user can view their own activity log.
     */
    public function viewOwnActivity(User $user): bool
    {
        return true;
    }
}
