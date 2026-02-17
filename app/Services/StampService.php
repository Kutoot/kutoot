<?php

namespace App\Services;

use App\Enums\StampSource;
use App\Events\StampsIssued;
use App\Models\Campaign;
use App\Models\Stamp;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

class StampService
{
    /**
     * Award stamps for a bill payment.
     * Number of stamps = floor(bill amount / 100) * plan's stamps_per_100.
     */
    public function awardStampsForBill(Transaction $transaction, ?int $campaignId = null): int
    {
        $user = $transaction->user;
        $campaign = $this->resolveCampaign($user, $campaignId);

        if (! $campaign) {
            return 0;
        }

        $plan = $this->getUserPlan($user);
        $stampsPerUnit = $plan?->stamps_per_100 ?? 1;

        $stampCount = (int) floor($transaction->amount / 100) * $stampsPerUnit;

        if ($stampCount <= 0) {
            return 0;
        }

        $this->createStamps($user, $campaign, $stampCount, StampSource::BillPayment, $transaction);

        return $stampCount;
    }

    /**
     * Award bonus stamps when a user purchases or upgrades a plan.
     */
    public function awardStampsForPlanPurchase(User $user, SubscriptionPlan $plan, ?int $campaignId = null): int
    {
        $campaign = $this->resolveCampaign($user, $campaignId);

        if (! $campaign || $plan->stamps_on_purchase <= 0) {
            return 0;
        }

        $this->createStamps($user, $campaign, $plan->stamps_on_purchase, StampSource::PlanPurchase);

        return $plan->stamps_on_purchase;
    }

    /**
     * Create multiple stamp records for a user + campaign.
     */
    protected function createStamps(
        User $user,
        Campaign $campaign,
        int $count,
        StampSource $source,
        ?Transaction $transaction = null,
    ): void {
        for ($i = 0; $i < $count; $i++) {
            Stamp::create([
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'transaction_id' => $transaction?->id,
                'code' => 'STP-'.strtoupper(Str::random(8)),
                'source' => $source,
            ]);
        }

        $campaign->increment('issued_stamps_cache', $count);

        StampsIssued::dispatch($campaign, $count);
    }

    protected function resolveCampaign(User $user, ?int $campaignId): ?Campaign
    {
        if ($campaignId) {
            return Campaign::find($campaignId);
        }

        if ($user->primary_campaign_id) {
            return $user->primaryCampaign;
        }

        return null;
    }

    protected function getUserPlan(User $user): ?SubscriptionPlan
    {
        $subscription = $user->effectiveSubscription();

        return $subscription?->plan_id ? SubscriptionPlan::find($subscription->plan_id) : null;
    }
}
