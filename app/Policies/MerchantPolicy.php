<?php

namespace App\Policies;

use App\Models\Merchant;
use App\Models\User;

class MerchantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-merchant');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Merchant $merchant): bool
    {
        return $user->can('view-merchant');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-merchant');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Merchant $merchant): bool
    {
        return $user->can('update-merchant');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Merchant $merchant): bool
    {
        return $user->can('delete-merchant');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Merchant $merchant): bool
    {
        return $user->can('restore-merchant');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Merchant $merchant): bool
    {
        return $user->can('force-delete-merchant');
    }
}
