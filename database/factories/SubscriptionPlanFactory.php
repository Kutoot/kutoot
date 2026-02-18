<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Basic', 'Silver', 'Gold', 'Platinum']),
            'price' => fake()->randomFloat(2, 0, 2999),
            'stamps_on_purchase' => fake()->numberBetween(0, 20),
            'stamps_per_100' => fake()->numberBetween(1, 5),
            'max_discounted_bills' => fake()->numberBetween(5, 50),
            'max_redeemable_amount' => fake()->randomFloat(2, 500, 10000),
            'duration_days' => fake()->randomElement([30, 60, 90, 180, 365]),
        ];
    }
}
