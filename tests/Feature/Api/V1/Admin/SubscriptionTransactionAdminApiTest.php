<?php

use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Super Admin');
});

// ── Subscription Plan CRUD ───────────────────────────────────────────────

it('lists subscription plans as admin', function () {
    Sanctum::actingAs($this->admin);

    SubscriptionPlan::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/subscription-plans')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a subscription plan', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/subscription-plans', [
        'name' => 'Gold Plan',
        'price' => 999,
        'stamps_on_purchase' => 10,
        'stamp_denomination' => 100,
        'stamps_per_denomination' => 1,
        'max_discounted_bills' => 20,
        'max_redeemable_amount' => 5000,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Gold Plan');
});

it('validates required fields for subscription plan', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/subscription-plans', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'price',
            'stamps_on_purchase',
            'stamp_denomination',
            'stamps_per_denomination',
            'max_discounted_bills',
            'max_redeemable_amount',
        ]);
});

it('denies subscription plan management to regular users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/subscription-plans')
        ->assertForbidden();
});

// ── User Subscriptions (read-only) ───────────────────────────────────────

it('lists user subscriptions as admin', function () {
    Sanctum::actingAs($this->admin);

    UserSubscription::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/user-subscriptions')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters user subscriptions by user', function () {
    Sanctum::actingAs($this->admin);

    $user = User::factory()->create();
    UserSubscription::factory()->count(2)->create(['user_id' => $user->id]);
    UserSubscription::factory()->create();

    $this->getJson('/api/v1/admin/user-subscriptions?filter[user_id]='.$user->id)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

// ── Transactions (read-only) ─────────────────────────────────────────────

it('lists transactions as admin', function () {
    Sanctum::actingAs($this->admin);

    Transaction::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/transactions')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters transactions by date range', function () {
    Sanctum::actingAs($this->admin);

    Transaction::factory()->create(['created_at' => now()->subDays(5)]);
    Transaction::factory()->create(['created_at' => now()->subDays(2)]);
    Transaction::factory()->create(['created_at' => now()]);

    $this->getJson('/api/v1/admin/transactions?filter[date_from]='.now()->subDays(3)->toDateString())
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('denies transaction viewing to regular users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/transactions')
        ->assertForbidden();
});
