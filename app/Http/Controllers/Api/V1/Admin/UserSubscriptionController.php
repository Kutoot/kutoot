<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSubscriptionResource;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Admin / User Subscriptions
 */
class UserSubscriptionController extends Controller
{
    /**
     * List all user subscriptions.
     *
     * @queryParam filter[user_id] int Filter by user.
     * @queryParam filter[plan_id] int Filter by plan.
     * @queryParam filter[status] string Filter by status (Active, Expired, Cancelled).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', UserSubscription::class);

        $subscriptions = UserSubscription::query()
            ->with(['user', 'plan'])
            ->when($request->input('filter.user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->input('filter.plan_id'), fn ($q, $id) => $q->where('plan_id', $id))
            ->when($request->input('filter.status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return UserSubscriptionResource::collection($subscriptions);
    }

    /**
     * Show a user subscription.
     */
    public function show(UserSubscription $userSubscription): UserSubscriptionResource
    {
        $this->authorize('view', $userSubscription);

        $userSubscription->load(['user', 'plan']);

        return new UserSubscriptionResource($userSubscription);
    }
}
