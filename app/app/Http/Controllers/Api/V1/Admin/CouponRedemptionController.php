<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponRedemptionResource;
use App\Models\CouponRedemption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Coupon Redemptions
 */
class CouponRedemptionController extends Controller
{
    /**
     * List all coupon redemptions.
     *
     * @queryParam filter[user_id] int Filter by user.
     * @queryParam filter[coupon_id] int Filter by coupon.
     * @queryParam filter[date_from] date Filter from date.
     * @queryParam filter[date_to] date Filter to date.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CouponRedemption::class);

        $redemptions = CouponRedemption::query()
            ->with(['coupon', 'user', 'transaction'])
            ->when($request->input('filter.user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->input('filter.coupon_id'), fn ($q, $id) => $q->where('coupon_id', $id))
            ->when($request->input('filter.date_from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->input('filter.date_to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CouponRedemptionResource::collection($redemptions);
    }

    /**
     * Show a coupon redemption.
     */
    public function show(CouponRedemption $couponRedemption): CouponRedemptionResource
    {
        $this->authorize('view', $couponRedemption);

        $couponRedemption->load(['coupon', 'user', 'transaction']);

        return new CouponRedemptionResource($couponRedemption);
    }
}
