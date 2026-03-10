<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CouponCategory extends Model
{
    /** @use HasFactory<\Database\Factories\CouponCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<DiscountCoupon, $this>
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(DiscountCoupon::class);
    }

    /**
     * @return BelongsToMany<SubscriptionPlan, $this>
     */
    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_coupon_category_access', 'coupon_category_id', 'plan_id');
    }
}
