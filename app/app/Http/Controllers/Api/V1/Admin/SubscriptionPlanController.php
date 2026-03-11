<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreSubscriptionPlanRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSubscriptionPlanRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / Subscription Plans
 */
class SubscriptionPlanController extends Controller
{
    /**
     * List all subscription plans.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SubscriptionPlan::class);

        $plans = SubscriptionPlan::query()
            ->with(['campaigns', 'couponCategories'])
            ->withCount('subscriptions')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return SubscriptionPlanResource::collection($plans);
    }

    /**
     * Show a subscription plan.
     */
    public function show(SubscriptionPlan $subscriptionPlan): SubscriptionPlanResource
    {
        $this->authorize('view', $subscriptionPlan);

        $subscriptionPlan->load(['campaigns', 'couponCategories']);
        $subscriptionPlan->loadCount('subscriptions');

        return new SubscriptionPlanResource($subscriptionPlan);
    }

    /**
     * Create a new subscription plan.
     */
    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $data = $request->validated();

        $plan = SubscriptionPlan::create($data);

        if (isset($data['campaigns'])) {
            $plan->campaigns()->sync($data['campaigns']);
        }

        if (isset($data['coupon_categories'])) {
            $plan->couponCategories()->sync($data['coupon_categories']);
        }

        $plan->load(['campaigns', 'couponCategories']);

        return (new SubscriptionPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a subscription plan.
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): SubscriptionPlanResource
    {
        $data = $request->validated();

        $subscriptionPlan->update($data);

        if (isset($data['campaigns'])) {
            $subscriptionPlan->campaigns()->sync($data['campaigns']);
        }

        if (isset($data['coupon_categories'])) {
            $subscriptionPlan->couponCategories()->sync($data['coupon_categories']);
        }

        $subscriptionPlan->load(['campaigns', 'couponCategories']);

        return new SubscriptionPlanResource($subscriptionPlan);
    }

    /**
     * Delete a subscription plan.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        $this->authorize('delete', $subscriptionPlan);

        $subscriptionPlan->delete();

        return response()->json(['message' => 'Subscription plan deleted.'], 200);
    }
}
