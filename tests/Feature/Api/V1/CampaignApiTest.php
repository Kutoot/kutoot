<?php

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ── Public Campaign Listing ──────────────────────────────────────────────

it('lists campaigns publicly', function () {
    Campaign::factory()->count(3)->create();

    $this->getJson('/api/v1/campaigns')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters campaigns by category', function () {
    $category = CampaignCategory::factory()->create();
    Campaign::factory()->count(2)->create(['category_id' => $category->id]);
    Campaign::factory()->create();

    $this->getJson('/api/v1/campaigns?filter[category_id]='.$category->id)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('filters campaigns by status', function () {
    Campaign::factory()->count(2)->create(['status' => CampaignStatus::Active]);
    Campaign::factory()->create(['status' => CampaignStatus::Paused]);

    $this->getJson('/api/v1/campaigns?filter[status]=active')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('shows a single campaign', function () {
    $campaign = Campaign::factory()->create();

    $this->getJson('/api/v1/campaigns/'.$campaign->id)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $campaign->id)
        ->assertJsonPath('data.reward_name', $campaign->reward_name);
});

it('returns 404 for non-existent campaign', function () {
    $this->getJson('/api/v1/campaigns/99999')
        ->assertNotFound();
});

// ── Campaign Bounty (Authenticated) ─────────────────────────────────────

it('returns bounty data for a campaign', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $campaign = Campaign::factory()->create();

    $this->getJson('/api/v1/campaigns/'.$campaign->id.'/bounty')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['organic_progress', 'marketing_boost', 'total_bounty']]);
});

it('requires auth for bounty endpoint', function () {
    $campaign = Campaign::factory()->create();

    $this->getJson('/api/v1/campaigns/'.$campaign->id.'/bounty')
        ->assertUnauthorized();
});
