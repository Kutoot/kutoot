<?php

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('lists user transactions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Transaction::factory()->count(3)->create(['user_id' => $user->id]);

    $this->getJson('/api/v1/transactions')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('does not show other users transactions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    Transaction::factory()->count(2)->create(['user_id' => $other->id]);

    $this->getJson('/api/v1/transactions')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('filters transactions by type', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Transaction::factory()->count(2)->create([
        'user_id' => $user->id,
        'type' => TransactionType::CouponRedemption,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::PlanPurchase,
    ]);

    $this->getJson('/api/v1/transactions?filter[type]=CouponRedemption')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('shows a single transaction owned by user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $transaction = Transaction::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/v1/transactions/'.$transaction->id)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $transaction->id);
});

it('returns 403 for another users transaction', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    $transaction = Transaction::factory()->create(['user_id' => $other->id]);

    $this->getJson('/api/v1/transactions/'.$transaction->id)
        ->assertForbidden();
});

it('requires auth for transaction listing', function () {
    $this->getJson('/api/v1/transactions')
        ->assertUnauthorized();
});
