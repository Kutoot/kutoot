<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreDiscountCouponRequest;
use App\Http\Requests\Api\V1\Admin\UpdateDiscountCouponRequest;
use App\Http\Resources\DiscountCouponResource;
use App\Models\DiscountCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Discount Coupons
 */
class DiscountCouponController extends Controller
{
    /**
     * List all discount coupons.
     *
     * @queryParam filter[category_id] int Filter by coupon category.
     * @queryParam filter[merchant_location_id] int Filter by merchant location.
     * @queryParam filter[is_active] boolean Filter by active status.
     * @queryParam search string Search by title or code.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DiscountCoupon::class);

        $coupons = DiscountCoupon::query()
            ->with(['category', 'merchantLocation.merchant'])
            ->withCount('redemptions')
            ->when($request->input('filter.category_id'), fn ($q, $id) => $q->where('coupon_category_id', $id))
            ->when($request->input('filter.merchant_location_id'), fn ($q, $id) => $q->where('merchant_location_id', $id))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->when($request->input('search'), fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return DiscountCouponResource::collection($coupons);
    }

    /**
     * Show a discount coupon.
     */
    public function show(DiscountCoupon $coupon): DiscountCouponResource
    {
        $this->authorize('view', $coupon);

        $coupon->load(['category', 'merchantLocation.merchant']);
        $coupon->loadCount('redemptions');

        return new DiscountCouponResource($coupon);
    }

    /**
     * Create a new discount coupon.
     */
    public function store(StoreDiscountCouponRequest $request): JsonResponse
    {
        $coupon = DiscountCoupon::create($request->validated());

        $coupon->load(['category', 'merchantLocation.merchant']);

        return (new DiscountCouponResource($coupon))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a discount coupon.
     */
    public function update(UpdateDiscountCouponRequest $request, DiscountCoupon $coupon): DiscountCouponResource
    {
        $coupon->update($request->validated());

        $coupon->load(['category', 'merchantLocation.merchant']);

        return new DiscountCouponResource($coupon);
    }

    /**
     * Delete a discount coupon.
     */
    public function destroy(DiscountCoupon $coupon): JsonResponse
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return response()->json(['message' => 'Discount coupon deleted.'], 200);
    }
}
