<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'payment_status' => $this->payment_status,
            'amount' => (float) $this->amount,
            'original_bill_amount' => (float) $this->original_bill_amount,
            'discount_amount' => (float) $this->discount_amount,
            'platform_fee' => (float) $this->platform_fee,
            'gst_amount' => (float) $this->gst_amount,
            'total_amount' => (float) $this->total_amount,
            'commission_amount' => (float) $this->commission_amount,
            'payment_gateway' => $this->payment_gateway,
            'payment_id' => $this->payment_id,
            'razorpay_order_id' => $this->razorpay_order_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'coupon' => new DiscountCouponResource($this->whenLoaded('coupon')),
            'merchant_location' => new MerchantLocationResource($this->whenLoaded('merchantLocation')),
            'stamps' => StampResource::collection($this->whenLoaded('stamps')),
            'coupon_redemption' => new CouponRedemptionResource($this->whenLoaded('couponRedemption')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
