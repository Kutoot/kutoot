<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasLabel
{
    case CouponRedemption = 'coupon_redemption';
    case PlanPurchase = 'plan_purchase';

    public function getLabel(): string
    {
        return match ($this) {
            self::CouponRedemption => 'Coupon Redemption',
            self::PlanPurchase => 'Plan Purchase',
        };
    }
}
