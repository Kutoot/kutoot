<?php

namespace App\Policies;

use App\Models\CouponCategory;
use App\Models\User;

class CouponCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-coupon-category');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CouponCategory $couponCategory): bool
    {
        return $user->can('view-coupon-category');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-coupon-category');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CouponCategory $couponCategory): bool
    {
        return $user->can('update-coupon-category');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CouponCategory $couponCategory): bool
    {
        return $user->can('delete-coupon-category');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CouponCategory $couponCategory): bool
    {
        return $user->can('restore-coupon-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CouponCategory $couponCategory): bool
    {
        return $user->can('force-delete-coupon-category');
    }
}
