<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\MerchantLocation
 */
class MerchantLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_name' => $this->branch_name,
            'commission_percentage' => (float) $this->commission_percentage,
            'is_active' => $this->is_active,
            'monthly_target_type' => $this->monthly_target_type,
            'monthly_target_value' => $this->monthly_target_value ? (float) $this->monthly_target_value : null,
            'deduct_commission_from_target' => $this->deduct_commission_from_target,
            'merchant' => new MerchantResource($this->whenLoaded('merchant')),
            'qr_codes_count' => $this->whenCounted('qrCodes'),
            'transactions_count' => $this->whenCounted('transactions'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
