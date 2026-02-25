<?php

use App\Models\Merchant;
use App\Models\MerchantLocation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('lists merchant locations', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    MerchantLocation::factory()->count(3)->create(['is_active' => true]);

    $this->getJson('/api/v1/merchant-locations')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters merchant locations by merchant', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $merchant = Merchant::factory()->create();
    MerchantLocation::factory()->count(2)->create([
        'merchant_id' => $merchant->id,
        'is_active' => true,
    ]);
    MerchantLocation::factory()->create(['is_active' => true]);

    $this->getJson('/api/v1/merchant-locations?filter[merchant_id]='.$merchant->id)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('searches merchant locations by branch name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    MerchantLocation::factory()->create([
        'branch_name' => 'Downtown Branch',
        'is_active' => true,
    ]);
    MerchantLocation::factory()->create([
        'branch_name' => 'Airport Branch',
        'is_active' => true,
    ]);

    $this->getJson('/api/v1/merchant-locations?search=Downtown')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('requires auth for merchant locations', function () {
    $this->getJson('/api/v1/merchant-locations')
        ->assertUnauthorized();
});
