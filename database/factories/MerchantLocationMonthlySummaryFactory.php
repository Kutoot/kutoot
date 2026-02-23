<?php

namespace Database\Factories;

use App\Models\MerchantLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MerchantLocationMonthlySummary>
 */
class MerchantLocationMonthlySummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_location_id' => MerchantLocation::factory(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('m'),
            'total_bill_amount' => fake()->randomFloat(2, 1000, 100000),
            'total_commission_amount' => fake()->randomFloat(2, 100, 10000),
            'net_amount' => fake()->randomFloat(2, 900, 90000),
            'transaction_count' => fake()->numberBetween(5, 200),
            'target_met' => fake()->boolean(70),
        ];
    }

    public function targetMet(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_met' => true,
        ]);
    }

    public function targetNotMet(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_met' => false,
        ]);
    }
}
