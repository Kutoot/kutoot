<?php

namespace App\Policies;

use App\Models\MerchantLocation;
use App\Models\User;

class MerchantLocationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-merchant-location');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MerchantLocation $merchantLocation): bool
    {
        return $user->can('view-merchant-location') && $user->merchantLocations->contains($merchantLocation);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-merchant-location');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MerchantLocation $merchantLocation): bool
    {
        return $user->can('update-merchant-location') && $user->merchantLocations->contains($merchantLocation);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MerchantLocation $merchantLocation): bool
    {
        return $user->can('delete-merchant-location');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MerchantLocation $merchantLocation): bool
    {
        return $user->can('restore-merchant-location');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MerchantLocation $merchantLocation): bool
    {
        return $user->can('force-delete-merchant-location');
    }
}
