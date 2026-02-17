<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class BasePlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['name' => 'Base Plan'],
            [
                'stamps_on_purchase' => 0,
                'stamps_per_100' => 1,
                'max_discounted_bills' => 5,
                'max_redeemable_amount' => 500,
                'is_default' => true,
                'duration_days' => null,
            ]
        );
    }
}
