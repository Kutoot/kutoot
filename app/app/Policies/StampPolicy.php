<?php

namespace App\Policies;

use App\Models\Stamp;
use App\Models\User;

class StampPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-stamp');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Stamp $stamp): bool
    {
        return $user->can('view-stamp');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-stamp');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Stamp $stamp): bool
    {
        return $user->can('update-stamp');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Stamp $stamp): bool
    {
        return $user->can('delete-stamp');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Stamp $stamp): bool
    {
        return $user->can('restore-stamp');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Stamp $stamp): bool
    {
        return $user->can('force-delete-stamp');
    }
}
