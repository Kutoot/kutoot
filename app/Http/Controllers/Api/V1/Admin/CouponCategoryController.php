<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCouponCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCouponCategoryRequest;
use App\Http\Resources\CouponCategoryResource;
use App\Models\CouponCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Coupon Categories
 */
class CouponCategoryController extends Controller
{
    /**
     * List all coupon categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CouponCategory::class);

        $categories = CouponCategory::query()
            ->with('subscriptionPlans')
            ->withCount('coupons')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CouponCategoryResource::collection($categories);
    }

    /**
     * Show a coupon category.
     */
    public function show(CouponCategory $couponCategory): CouponCategoryResource
    {
        $this->authorize('view', $couponCategory);

        $couponCategory->load('subscriptionPlans');
        $couponCategory->loadCount('coupons');

        return new CouponCategoryResource($couponCategory);
    }

    /**
     * Create a new coupon category.
     */
    public function store(StoreCouponCategoryRequest $request): JsonResponse
    {
        $category = CouponCategory::create($request->validated());

        return (new CouponCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a coupon category.
     */
    public function update(UpdateCouponCategoryRequest $request, CouponCategory $couponCategory): CouponCategoryResource
    {
        $couponCategory->update($request->validated());

        return new CouponCategoryResource($couponCategory);
    }

    /**
     * Delete a coupon category.
     */
    public function destroy(CouponCategory $couponCategory): JsonResponse
    {
        $this->authorize('delete', $couponCategory);

        $couponCategory->delete();

        return response()->json(['message' => 'Coupon category deleted.'], 200);
    }
}
