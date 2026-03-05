<?php

use App\Enums\DiscountType;
use App\Enums\SubscriptionStatus;
use App\Models\CouponCategory;
use App\Models\DiscountCoupon;
use App\Models\MerchantLocation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->plan = SubscriptionPlan::factory()->create([
        'name' => 'Test Plan',
        'price' => 99,
        'is_default' => false,
        'max_discounted_bills' => 3,
        'max_redeemable_amount' => 500.00,
        'stamp_denomination' => 100,
        'stamps_per_denomination' => 1,
        'stamps_on_purchase' => 5,
        'duration_days' => 30,
    ]);

    $this->category = CouponCategory::factory()->create(['name' => 'Food']);
    $this->category->subscriptionPlans()->attach($this->plan->id);

    $this->location = MerchantLocation::factory()->create();

    $this->coupon = DiscountCoupon::factory()->create([
        'coupon_category_id' => $this->category->id,
        'merchant_location_id' => $this->location->id,
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 100,
        'min_order_value' => 50,
        'usage_per_user' => 10,
    ]);

    $this->user = User::factory()->create();
});

// ── Bill Limit Enforcement ───────────────────────────────────────────────

it('blocks coupon redemption when bill limit is reached', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 3,
        'amount_redeemed' => 200.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson('/api/v1/coupons/'.$this->coupon->id.'/redeem', [
        'merchant_location_id' => $this->location->id,
        'amount' => 500,
    ])->assertStatus(422)
        ->assertJsonFragment(['error' => 'You have used all 3 discounted bills for your plan period.']);
});

it('blocks coupon redemption when max redeemable amount is reached', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 1,
        'amount_redeemed' => 500.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson('/api/v1/coupons/'.$this->coupon->id.'/redeem', [
        'merchant_location_id' => $this->location->id,
        'amount' => 500,
    ])->assertStatus(422)
        ->assertJsonFragment(['error' => 'You have reached your maximum redeemable discount for this plan period.']);
});

it('allows coupon redemption when within bill limits', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 1,
        'amount_redeemed' => 100.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    // This should proceed (may fail at Razorpay but not at validation)
    $response = $this->postJson('/api/v1/coupons/'.$this->coupon->id.'/redeem', [
        'merchant_location_id' => $this->location->id,
        'amount' => 500,
    ]);

    // Should not be a 422 bill-limit error (it may succeed or fail at payment, not at limit check)
    if ($response->status() === 422) {
        $error = $response->json('error');
        expect($error)->not->toContain('discounted bills')
            ->not->toContain('maximum redeemable discount');
    }
});

// ── Counter Increment on Zero-Amount Redemption ─────────────────────────

it('increments billing counters on zero-amount coupon redemption', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 0,
        'amount_redeemed' => 0,
        'expires_at' => now()->addDays(30),
    ]);

    // Create a coupon with a discount larger than the bill + fees so grand total is zero
    $freeCoupon = DiscountCoupon::factory()->create([
        'coupon_category_id' => $this->category->id,
        'merchant_location_id' => $this->location->id,
        'discount_type' => DiscountType::Fixed,
        'discount_value' => 10000,
        'min_order_value' => null,
        'usage_per_user' => 10,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/coupons/'.$freeCoupon->id.'/redeem', [
        'merchant_location_id' => $this->location->id,
        'amount' => 100,
    ]);

    // If zero_amount path, counters update immediately
    // If paid path (platform fee makes total > 0), counters update on verifyPayment
    if ($response->json('zero_amount') === true) {
        $response->assertSuccessful();
        $subscription->refresh();
        expect($subscription->bills_used)->toBe(1)
            ->and((float) $subscription->amount_redeemed)->toBeGreaterThan(0);
    } else {
        $response->assertSuccessful();
        expect($response->json('transaction_id'))->not->toBeNull();
    }
});

// ── Counter Reset on Plan Upgrade ────────────────────────────────────────

it('resets billing counters when user upgrades plan', function () {
    $oldSubscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 3,
        'amount_redeemed' => 450.00,
        'expires_at' => now()->addDays(10),
    ]);

    $newPlan = SubscriptionPlan::factory()->create([
        'name' => 'Premium Plan',
        'price' => 299,
        'max_discounted_bills' => 10,
        'max_redeemable_amount' => 2000.00,
        'duration_days' => 90,
    ]);

    $subscriptionService = app(\App\Services\SubscriptionService::class);
    $newSubscription = $subscriptionService->upgradePlan($this->user, $newPlan->id);

    // Old subscription should be expired
    $oldSubscription->refresh();
    expect($oldSubscription->status)->toBe(SubscriptionStatus::Expired);

    // New subscription has fresh counters (refresh to load DB defaults)
    $newSubscription->refresh();
    expect((int) $newSubscription->bills_used)->toBe(0)
        ->and((float) $newSubscription->amount_redeemed)->toBe(0.0);
});

// ── Zero/Null Limits Treated as Unlimited ────────────────────────────────

it('treats zero max_discounted_bills as unlimited', function () {
    $unlimitedPlan = SubscriptionPlan::factory()->create([
        'name' => 'Unlimited Plan',
        'price' => 999,
        'max_discounted_bills' => 0,
        'max_redeemable_amount' => 0,
        'duration_days' => 30,
    ]);

    $this->category->subscriptionPlans()->attach($unlimitedPlan->id);

    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $unlimitedPlan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 100,
        'amount_redeemed' => 50000.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    // Calculate should not show bill limit or redeemable amount warnings
    $response = $this->postJson('/api/v1/coupons/calculate', [
        'bill_amount' => 500,
        'merchant_location_id' => $this->location->id,
    ]);

    // Even with 100 bills used and 50k redeemed, 0 means unlimited — should not be blocked
    $response->assertStatus(200);
    $responseData = $response->json();

    // Should not contain billing limit error messages
    $json = json_encode($responseData);
    expect($json)->not->toContain('discounted bills')
        ->and($json)->not->toContain('maximum redeemable discount');
});

// ── Calculate Endpoint Warning ──────────────────────────────────────────

it('shows warning in calculate when bill limit is reached', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 3,
        'amount_redeemed' => 0,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/coupons/calculate', [
        'bill_amount' => 500,
        'merchant_location_id' => $this->location->id,
        'coupon_id' => $this->coupon->id,
    ]);

    $response->assertSuccessful();
    expect($response->json('warnings'))->toContain('You have used all 3 discounted bills for your plan.');
});

it('shows warning in calculate when max redeemable amount is reached', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 0,
        'amount_redeemed' => 500.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/coupons/calculate', [
        'bill_amount' => 500,
        'merchant_location_id' => $this->location->id,
        'coupon_id' => $this->coupon->id,
    ]);

    $response->assertSuccessful();
    expect($response->json('warnings'))->toContain('You have reached your maximum redeemable discount for this plan period.');
});

// ── Dashboard Remaining Limits ──────────────────────────────────────────

it('dashboard shows remaining limits from subscription counters', function () {
    $subscription = UserSubscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'bills_used' => 2,
        'amount_redeemed' => 300.00,
        'expires_at' => now()->addDays(30),
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertSuccessful();
    $stats = $response->json('data.stats');

    expect($stats['total_coupons_used'])->toBe(2)
        ->and($stats['remaining_bills'])->toBe(1)
        ->and((float) $stats['remaining_redeem_amount'])->toBe(200.0);
});
