<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\LoanTier;
use App\Models\MerchantLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MerchantLocationLoan>
 */
class MerchantLocationLoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_location_id' => MerchantLocation::factory(),
            'loan_tier_id' => LoanTier::factory(),
            'amount' => fake()->randomFloat(2, 10000, 200000),
            'status' => LoanStatus::Active,
            'streak_months_at_approval' => fake()->numberBetween(3, 12),
            'approved_at' => now(),
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Completed,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Paused,
            'streak_broken_at' => now(),
        ]);
    }
}
