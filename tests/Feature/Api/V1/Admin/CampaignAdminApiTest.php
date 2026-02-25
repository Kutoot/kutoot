<?php

use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Super Admin');
});

// ── Campaign CRUD ────────────────────────────────────────────────────────

it('lists campaigns as admin', function () {
    Sanctum::actingAs($this->admin);

    Campaign::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/campaigns')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a campaign as admin', function () {
    Sanctum::actingAs($this->admin);

    $category = CampaignCategory::factory()->create();

    $this->postJson('/api/v1/admin/campaigns', [
        'category_id' => $category->id,
        'creator_type' => 'merchant',
        'creator_id' => $this->admin->id,
        'reward_name' => 'Test Campaign',
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'reward_cost_target' => 5000,
        'stamp_target' => 50,
    ])->assertCreated()
        ->assertJsonPath('data.reward_name', 'Test Campaign');
});

it('validates required fields when creating a campaign', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/campaigns', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'category_id',
            'creator_type',
            'creator_id',
            'reward_name',
            'status',
            'start_date',
            'reward_cost_target',
            'stamp_target',
        ]);
});

it('updates a campaign as admin', function () {
    Sanctum::actingAs($this->admin);

    $campaign = Campaign::factory()->create(['creator_id' => $this->admin->id]);

    $this->putJson('/api/v1/admin/campaigns/'.$campaign->id, [
        'reward_name' => 'Updated Campaign',
    ])->assertSuccessful()
        ->assertJsonPath('data.reward_name', 'Updated Campaign');
});

it('deletes a campaign as admin', function () {
    Sanctum::actingAs($this->admin);

    $campaign = Campaign::factory()->create(['creator_id' => $this->admin->id]);

    $this->deleteJson('/api/v1/admin/campaigns/'.$campaign->id)
        ->assertSuccessful();

    $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
});

it('denies campaign management to regular users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/campaigns')
        ->assertForbidden();
});

// ── Campaign Category CRUD ───────────────────────────────────────────────

it('creates a campaign category', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/admin/campaign-categories', [
        'name' => 'Test Category',
        'slug' => 'test-category',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Test Category');
});

it('lists campaign categories', function () {
    Sanctum::actingAs($this->admin);

    CampaignCategory::factory()->count(3)->create();

    $this->getJson('/api/v1/admin/campaign-categories')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('validates unique slug for campaign categories', function () {
    Sanctum::actingAs($this->admin);

    CampaignCategory::factory()->create(['slug' => 'existing-slug']);

    $this->postJson('/api/v1/admin/campaign-categories', [
        'name' => 'Another',
        'slug' => 'existing-slug',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});
