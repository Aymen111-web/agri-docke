<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Anyone can browse categories (public routes — no policy needed).
     * This method is here for completeness if used via authorize().
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone can view a single category.
     */
    public function view(?User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Admin-only: create a category.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: update a category.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->is_admin;
    }

    /**
     * Admin-only: delete a category.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->is_admin;
    }
}
