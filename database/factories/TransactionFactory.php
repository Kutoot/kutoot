<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\MerchantLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalBill = fake()->randomFloat(2, 100, 1000);
        $discount = fake()->randomFloat(2, 10, min(100, $originalBill));
        $amount = max(0, $originalBill - $discount);
        $platformFee = 10.00;
        $gst = round($platformFee * 0.18, 2);

        return [
            'user_id' => User::factory(),
            'merchant_location_id' => MerchantLocation::factory(),
            'original_bill_amount' => $originalBill,
            'discount_amount' => $discount,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'gst_amount' => $gst,
            'total_amount' => $amount + $platformFee + $gst,
            'commission_amount' => round($amount * 0.05, 2),
            'payment_status' => PaymentStatus::Pending,
            'type' => TransactionType::CouponRedemption,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'payment_id' => 'pay_'.fake()->regexify('[A-Za-z0-9]{14}'),
            'razorpay_order_id' => 'order_'.fake()->regexify('[A-Za-z0-9]{14}'),
        ]);
    }

    public function planPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::PlanPurchase,
            'merchant_location_id' => null,
            'coupon_id' => null,
            'discount_amount' => 0,
            'platform_fee' => 0,
            'commission_amount' => 0,
            'payment_gateway' => 'razorpay',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Completed,
            'payment_id' => 'pay_'.fake()->regexify('[A-Za-z0-9]{14}'),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Refunded,
            'refund_id' => 'rfnd_'.fake()->regexify('[A-Za-z0-9]{14}'),
        ]);
    }
}
