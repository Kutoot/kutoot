<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSubscription;

class UserSubscriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-user-subscription');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserSubscription $userSubscription): bool
    {
        return $user->can('view-user-subscription');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-user-subscription');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserSubscription $userSubscription): bool
    {
        return $user->can('update-user-subscription');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserSubscription $userSubscription): bool
    {
        return $user->can('delete-user-subscription');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserSubscription $userSubscription): bool
    {
        return $user->can('restore-user-subscription');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserSubscription $userSubscription): bool
    {
        return $user->can('force-delete-user-subscription');
    }
}
