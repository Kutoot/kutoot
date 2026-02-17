<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;

class SubscriptionService
{
    public function __construct(
        protected StampService $stampService,
    ) {}

    /**
     * Upgrade (or purchase) a plan for the user.
     * Expires existing active subscriptions and creates a new one.
     * Awards bonus stamps if user has a primary campaign set.
     */
    public function upgradePlan(User $user, int $planId): UserSubscription
    {
        // Expire existing active subscriptions
        $user->subscriptions()->where('status', SubscriptionStatus::Active)->update([
            'status' => SubscriptionStatus::Expired,
        ]);

        // Create new subscription
        $plan = SubscriptionPlan::find($planId);
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'status' => SubscriptionStatus::Active,
            'expires_at' => $plan?->duration_days ? now()->addDays($plan->duration_days) : null,
        ]);
        if ($plan && $plan->stamps_on_purchase > 0 && $user->primary_campaign_id) {
            $this->stampService->awardStampsForPlanPurchase($user, $plan);
        }

        // If current primary campaign is not in the new plan, clear it
        if ($user->primary_campaign_id && $plan) {
            $campaignInPlan = $plan->campaigns()->where('campaigns.id', $user->primary_campaign_id)->exists();
            if (! $campaignInPlan) {
                $user->update(['primary_campaign_id' => null]);
            }
        }

        return $subscription;
    }

    /**
     * Set the user's primary campaign (must be accessible from their current plan).
     */
    public function setPrimaryCampaign(User $user, int $campaignId): bool
    {
        $subscription = $user->effectiveSubscription();

        if (! $subscription) {
            return false;
        }

        $plan = SubscriptionPlan::find($subscription->plan_id);

        if (! $plan) {
            return false;
        }

        // Verify the campaign is accessible under the user's plan
        $isAccessible = $plan->campaigns()->where('campaigns.id', $campaignId)->exists();

        if (! $isAccessible) {
            return false;
        }

        $user->update(['primary_campaign_id' => $campaignId]);

        // Award plan purchase stamps if this is a first-time campaign selection after plan purchase
        // and stamps haven't been awarded yet
        if ($plan->stamps_on_purchase > 0) {
            $alreadyAwarded = $user->stamps()
                ->where('campaign_id', $campaignId)
                ->where('source', 'plan_purchase')
                ->exists();

            if (! $alreadyAwarded) {
                $this->stampService->awardStampsForPlanPurchase($user, $plan, $campaignId);
            }
        }

        return true;
    }

    public function revertToBasePlan(User $user): ?UserSubscription
    {
        $basePlan = SubscriptionPlan::where('is_default', true)->first();

        if (! $basePlan) {
            return null;
        }

        // Expire existing active subscriptions
        $user->subscriptions()->where('status', SubscriptionStatus::Active)->update([
            'status' => SubscriptionStatus::Expired,
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $basePlan->id,
            'status' => SubscriptionStatus::Active,
            'expires_at' => null,
        ]);

        // If current primary campaign is not in the base plan, clear it
        if ($user->primary_campaign_id) {
            $campaignInPlan = $basePlan->campaigns()->where('campaigns.id', $user->primary_campaign_id)->exists();
            if (! $campaignInPlan) {
                $user->update(['primary_campaign_id' => null]);
            }
        }

        return $subscription;
    }
}
