<?php

namespace App\Policies;

use App\Models\CouponRedemption;
use App\Models\User;

class CouponRedemptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-coupon-redemption');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CouponRedemption $couponRedemption): bool
    {
        return $user->can('view-coupon-redemption') && $user->merchantLocations->contains($couponRedemption->merchant_location_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-coupon-redemption');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CouponRedemption $couponRedemption): bool
    {
        return $user->can('update-coupon-redemption');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CouponRedemption $couponRedemption): bool
    {
        return $user->can('delete-coupon-redemption');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CouponRedemption $couponRedemption): bool
    {
        return $user->can('restore-coupon-redemption');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CouponRedemption $couponRedemption): bool
    {
        return $user->can('force-delete-coupon-redemption');
    }
}
