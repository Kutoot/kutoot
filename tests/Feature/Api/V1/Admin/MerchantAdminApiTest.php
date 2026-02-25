<?php

use App\Models\Merchant;
use App\Models\MerchantLocation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Super Admin');
});

// ── Merchant CRUD ────────────────────────────────────────────────────────

it('lists merchants as admin', function () {
    Sanctum::actingAs($this->admin);

    Merchant::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/merchants')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a merchant', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/merchants', [
        'name' => 'Test Merchant',
        'slug' => 'test-merchant',
        'is_active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Test Merchant');
});

it('validates required fields for merchant creation', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/merchants', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'is_active']);
});

it('validates unique slug for merchants', function () {
    Sanctum::actingAs($this->admin);

    Merchant::factory()->create(['slug' => 'taken-slug']);

    $this->postJson('/api/v1/admin/merchants', [
        'name' => 'Another',
        'slug' => 'taken-slug',
        'is_active' => true,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('updates a merchant', function () {
    Sanctum::actingAs($this->admin);

    $merchant = Merchant::factory()->create();

    $this->putJson('/api/v1/admin/merchants/'.$merchant->id, [
        'name' => 'Updated Merchant',
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Merchant');
});

it('deletes a merchant', function () {
    Sanctum::actingAs($this->admin);

    $merchant = Merchant::factory()->create();

    $this->deleteJson('/api/v1/admin/merchants/'.$merchant->id)
        ->assertSuccessful();
});

it('denies merchant management to regular users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/merchants')
        ->assertForbidden();
});

// ── Merchant Location CRUD ───────────────────────────────────────────────

it('lists merchant locations as admin', function () {
    Sanctum::actingAs($this->admin);

    MerchantLocation::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/merchant-locations')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a merchant location', function () {
    Sanctum::actingAs($this->admin);

    $merchant = Merchant::factory()->create();

    $this->postJson('/api/v1/admin/merchant-locations', [
        'merchant_id' => $merchant->id,
        'branch_name' => 'Test Branch',
        'commission_percentage' => 10,
        'is_active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.branch_name', 'Test Branch');
});

it('validates required fields for merchant location', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/merchant-locations', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['merchant_id', 'branch_name', 'commission_percentage', 'is_active']);
});

it('filters merchant locations by merchant', function () {
    Sanctum::actingAs($this->admin);

    $merchant = Merchant::factory()->create();
    MerchantLocation::factory()->count(2)->create(['merchant_id' => $merchant->id]);
    MerchantLocation::factory()->create();

    $this->getJson('/api/v1/admin/merchant-locations?filter[merchant_id]='.$merchant->id)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});
