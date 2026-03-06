<?php

namespace App\Listeners;

use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\StampService;
use Illuminate\Auth\Events\Registered;

class AssignBasePlanListener
{
    public function __construct(
        protected StampService $stampService,
    ) {}

    /**
     * Assign the default (base) plan to newly registered users.
     * If the base plan grants stamps_on_purchase, award them immediately.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        $basePlan = SubscriptionPlan::where('is_default', true)->first();

        if (! $basePlan) {
            return;
        }

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $basePlan->id,
            'status' => SubscriptionStatus::Active,
            'expires_at' => null,
        ]);

        // Award stamps if the base plan grants stamps on purchase.
        // resolveCampaign() will attempt user's primary campaign, then any
        // active subscribed campaign, or return null (no stamps awarded).
        if ($basePlan->stamps_on_purchase > 0) {
            $this->stampService->awardStampsForPlanPurchase($user, $basePlan);
        }
    }
}
