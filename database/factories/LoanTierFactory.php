<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LoanTier>
 */
class LoanTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'min_streak_months' => fake()->randomElement([3, 6, 9, 12]),
            'max_loan_amount' => fake()->randomFloat(2, 10000, 500000),
            'interest_rate_percentage' => fake()->randomFloat(2, 0, 18),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
