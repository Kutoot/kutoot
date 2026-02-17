<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentSubscription = $user->effectiveSubscription();
        $currentPlanId = $currentSubscription?->plan_id;

        $plans = SubscriptionPlan::query()
            ->with([
                'campaigns:id,reward_name,category_id,status',
                'campaigns.category:id,name',
                'couponCategories:id,name,icon',
            ])
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'is_default' => $plan->is_default,
                'stamps_on_purchase' => $plan->stamps_on_purchase,
                'stamps_per_100' => $plan->stamps_per_100,
                'max_discounted_bills' => $plan->max_discounted_bills,
                'max_redeemable_amount' => $plan->max_redeemable_amount,
                'campaigns' => $plan->campaigns->map(fn ($campaign) => [
                    'id' => $campaign->id,
                    'reward_name' => $campaign->reward_name,
                    'category_name' => $campaign->category?->name,
                    'status' => $campaign->status,
                ]),
                'coupon_categories' => $plan->couponCategories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                ]),
                'campaign_count' => $plan->campaigns->count(),
                'coupon_category_count' => $plan->couponCategories->count(),
            ]);

        // Get the campaigns available under the user's current plan for primary campaign selection
        $availableCampaigns = [];
        if ($currentPlanId) {
            $currentPlan = SubscriptionPlan::with('campaigns:id,reward_name')->find($currentPlanId);
            $availableCampaigns = $currentPlan?->campaigns->map(fn ($c) => [
                'id' => $c->id,
                'reward_name' => $c->reward_name,
            ])->toArray() ?? [];
        }

        return Inertia::render('Subscriptions/Index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription ? [
                'plan_id' => $currentSubscription->plan_id,
                'status' => $currentSubscription->status,
            ] : null,
            'primaryCampaignId' => $user->primary_campaign_id,
            'availableCampaigns' => $availableCampaigns,
        ]);
    }

    public function upgrade(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // No downgrade allowed — only upgrade to non-default plans
        if ($plan->is_default) {
            return back()->with('error', 'You cannot manually switch to the base plan.');
        }

        $this->subscriptionService->upgradePlan($user, $plan->id);

        // If user doesn't have a primary campaign, prompt them to choose one
        $user->refresh();
        if (! $user->primary_campaign_id) {
            return back()->with([
                'success' => 'Upgraded to '.$plan->name.' successfully! Please select your primary campaign.',
                'needsCampaignSelection' => true,
            ]);
        }

        return back()->with('success', 'Upgraded to '.$plan->name.' successfully!');
    }

    /**
     * Set the user's primary campaign from their plan's available campaigns.
     */
    public function setPrimaryCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
        ]);

        $user = $request->user();
        $success = $this->subscriptionService->setPrimaryCampaign($user, $validated['campaign_id']);

        if (! $success) {
            return back()->with('error', 'This campaign is not available under your current plan.');
        }

        return back()->with('success', 'Primary campaign set successfully!');
    }
}
