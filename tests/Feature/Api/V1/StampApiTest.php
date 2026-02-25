<?php

use App\Models\Campaign;
use App\Models\Stamp;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('lists user stamps', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Stamp::factory()->count(3)->create(['user_id' => $user->id]);

    $this->getJson('/api/v1/stamps')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('does not show other users stamps', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    Stamp::factory()->count(2)->create(['user_id' => $other->id]);

    $this->getJson('/api/v1/stamps')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('filters stamps by campaign', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $campaign = Campaign::factory()->create();
    Stamp::factory()->count(2)->create(['user_id' => $user->id, 'campaign_id' => $campaign->id]);
    Stamp::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/v1/stamps?filter[campaign_id]='.$campaign->id)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('requires auth for stamp listing', function () {
    $this->getJson('/api/v1/stamps')
        ->assertUnauthorized();
});
