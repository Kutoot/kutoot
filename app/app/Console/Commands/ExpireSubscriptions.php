<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire subscriptions past their expires_at date and revert users to the base plan';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $expired = UserSubscription::query()
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with('user')
            ->get();

        $count = 0;

        foreach ($expired as $subscription) {
            $subscription->update(['status' => SubscriptionStatus::Expired]);
            $subscriptionService->revertToBasePlan($subscription->user);
            $count++;
        }

        $this->info("Expired {$count} subscription(s) and reverted to base plan.");

        return self::SUCCESS;
    }
}
