<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seller\StoreMerchantCouponRequest;
use App\Http\Requests\Api\V1\Seller\UpdateMerchantCouponRequest;
use App\Http\Resources\DiscountCouponResource;
use App\Models\DiscountCoupon;
use App\Models\MerchantLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantLocationCouponController extends Controller
{
    /**
     * List coupons (deals) belonging to this merchant location.
     */
    public function index(Request $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $coupons = $merchantLocation->coupons()
            ->withCount('redemptions')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'deals' => DiscountCouponResource::collection($coupons),
            ],
        ]);
    }

    /**
     * Create a new coupon for this merchant location.
     */
    public function store(StoreMerchantCouponRequest $request, MerchantLocation $merchantLocation): JsonResponse
    {
        $data = $request->validated();
        $data['merchant_location_id'] = $merchantLocation->id;
        $data['is_active'] = false;
        $data['approval_status'] = ApprovalStatus::Pending;

        // Default coupon category if not provided
        if (empty($data['coupon_category_id'])) {
            $data['coupon_category_id'] = \App\Models\CouponCategory::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General', 'icon' => 'heroicon-o-tag']
            )->id;
        }

        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = strtoupper(substr($merchantLocation->branch_name, 0, 4))
                .strtoupper(substr(uniqid(), -6));
        }

        // Default starts_at to now if not provided
        if (empty($data['starts_at'])) {
            $data['starts_at'] = now();
        }

        $coupon = DiscountCoupon::query()->create($data);
        $coupon->loadCount('redemptions');

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully. It will be visible to customers once approved by admin.',
            'data' => new DiscountCouponResource($coupon),
        ], 201);
    }

    /**
     * Update an existing coupon belonging to this merchant location.
     */
    public function update(UpdateMerchantCouponRequest $request, MerchantLocation $merchantLocation, DiscountCoupon $coupon): JsonResponse
    {
        // Ensure coupon belongs to this location
        if ((int) $coupon->merchant_location_id !== (int) $merchantLocation->id) {
            return response()->json([
                'success' => false,
                'message' => 'This deal does not belong to your store.',
            ], 403);
        }

        $coupon->update($request->validated());

        // If previously rejected, resubmission resets to pending for re-review
        if ($coupon->approval_status === ApprovalStatus::Rejected) {
            $coupon->update([
                'approval_status' => ApprovalStatus::Pending,
                'rejection_reason' => null,
            ]);
        }

        $coupon->loadCount('redemptions');

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully.',
            'data' => new DiscountCouponResource($coupon),
        ]);
    }

    /**
     * Deactivate (soft-delete) a coupon belonging to this merchant location.
     */
    public function destroy(Request $request, MerchantLocation $merchantLocation, DiscountCoupon $coupon): JsonResponse
    {
        // Ensure coupon belongs to this location
        if ((int) $coupon->merchant_location_id !== (int) $merchantLocation->id) {
            return response()->json([
                'success' => false,
                'message' => 'This deal does not belong to your store.',
            ], 403);
        }

        $coupon->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Deal deactivated successfully.',
        ]);
    }
}
