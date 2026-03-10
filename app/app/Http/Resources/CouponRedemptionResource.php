<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CouponRedemption
 */
class CouponRedemptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'discount_applied' => (float) $this->discount_applied,
            'original_bill_amount' => (float) $this->original_bill_amount,
            'platform_fee' => (float) $this->platform_fee,
            'gst_amount' => (float) $this->gst_amount,
            'total_paid' => (float) $this->total_paid,
            'coupon' => new DiscountCouponResource($this->whenLoaded('coupon')),
            'user' => new UserResource($this->whenLoaded('user')),
            'transaction' => new TransactionResource($this->whenLoaded('transaction')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
