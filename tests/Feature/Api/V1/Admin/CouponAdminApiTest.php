<?php

use App\Models\CouponCategory;
use App\Models\CouponRedemption;
use App\Models\DiscountCoupon;
use App\Models\MerchantLocation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Super Admin');
});

// ── Discount Coupon CRUD ─────────────────────────────────────────────────

it('lists coupons as admin', function () {
    Sanctum::actingAs($this->admin);

    DiscountCoupon::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/coupons')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a coupon', function () {
    Sanctum::actingAs($this->admin);

    $category = CouponCategory::factory()->create();
    $location = MerchantLocation::factory()->create();

    $this->postJson('/api/v1/admin/coupons', [
        'coupon_category_id' => $category->id,
        'merchant_location_id' => $location->id,
        'title' => 'Test Coupon',
        'discount_type' => 'fixed',
        'discount_value' => 100,
        'code' => 'TEST100',
        'usage_per_user' => 1,
        'starts_at' => now()->toDateTimeString(),
        'is_active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.title', 'Test Coupon');
});

it('validates required fields for coupon creation', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/coupons', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'coupon_category_id',
            'title',
            'discount_type',
            'discount_value',
            'code',
            'usage_per_user',
            'starts_at',
            'is_active',
        ]);
});

it('validates expires_at is after starts_at', function () {
    Sanctum::actingAs($this->admin);

    $category = CouponCategory::factory()->create();

    $this->postJson('/api/v1/admin/coupons', [
        'coupon_category_id' => $category->id,
        'title' => 'Expired Coupon',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'code' => 'EXP50',
        'usage_per_user' => 1,
        'starts_at' => now()->addDay()->toDateTimeString(),
        'expires_at' => now()->toDateTimeString(),
        'is_active' => true,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['expires_at']);
});

it('denies coupon management to regular users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/coupons')
        ->assertForbidden();
});

// ── Coupon Category CRUD ─────────────────────────────────────────────────

it('creates a coupon category', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/coupon-categories', [
        'name' => 'Food',
        'slug' => 'food',
        'is_active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Food');
});

it('lists coupon categories', function () {
    Sanctum::actingAs($this->admin);

    CouponCategory::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/coupon-categories')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

// ── Coupon Redemptions (read-only) ───────────────────────────────────────

it('lists coupon redemptions as admin', function () {
    Sanctum::actingAs($this->admin);

    CouponRedemption::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/coupon-redemptions')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('shows a single coupon redemption', function () {
    Sanctum::actingAs($this->admin);

    $redemption = CouponRedemption::factory()->create();

    $this->getJson('/api/v1/admin/coupon-redemptions/'.$redemption->id)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $redemption->id);
});
