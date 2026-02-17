<?php

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-qr-code');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QrCode $qrCode): bool
    {
        return $user->can('view-qr-code') && $user->merchantLocations->contains($qrCode->merchant_location_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-qr-code');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QrCode $qrCode): bool
    {
        return $user->can('update-qr-code') && $user->merchantLocations->contains($qrCode->merchant_location_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QrCode $qrCode): bool
    {
        return $user->can('delete-qr-code');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QrCode $qrCode): bool
    {
        return $user->can('restore-qr-code');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QrCode $qrCode): bool
    {
        return $user->can('force-delete-qr-code');
    }
}
