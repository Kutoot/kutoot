<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seller\UpdateMerchantLocationBankRequest;
use App\Models\MerchantLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantLocationSettingsController extends Controller
{
    /**
     * Master admin settings (commission, discount, etc.).
     */
    public function masterAdminSettings(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'commissionPercent' => (float) $merchantLocation->commission_percentage,
                'discountPercent' => (float) config('kutoot.default_discount_percent', 10),
                'monthlyTargetType' => $merchantLocation->monthly_target_type?->value,
                'monthlyTargetValue' => $merchantLocation->monthly_target_value ? (float) $merchantLocation->monthly_target_value : null,
            ],
        ]);
    }

    /**
     * Get bank details of the merchant location.
     */
    public function bankDetails(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'bankName' => $merchantLocation->bank_name,
                'accountNumber' => $merchantLocation->account_number,
                'ifsc' => $merchantLocation->ifsc_code,
                'upiId' => $merchantLocation->upi_id,
                'beneficiaryName' => $merchantLocation->sub_bank_name,
            ],
        ]);
    }

    /**
     * Update bank details of the merchant location.
     */
    public function updateBankDetails(UpdateMerchantLocationBankRequest $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $merchantLocation->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bank details updated successfully.',
            'data' => [
                'bankName' => $merchantLocation->bank_name,
                'accountNumber' => $merchantLocation->account_number,
                'ifsc' => $merchantLocation->ifsc_code,
                'upiId' => $merchantLocation->upi_id,
                'beneficiaryName' => $merchantLocation->sub_bank_name,
            ],
        ]);
    }
}
