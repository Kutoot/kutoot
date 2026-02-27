<?php

use App\Models\FeaturedBanner;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
});

// ── Public endpoint ──────────────────────────────────────────────────────

it('returns active featured banners publicly without auth', function () {
    FeaturedBanner::factory()->count(2)->create(['is_active' => true]);
    FeaturedBanner::factory()->create(['is_active' => false]);

    getJson('/api/v1/featured-banners')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'link_url', 'link_text', 'sort_order', 'is_active', 'image_url', 'thumb_url', 'preview_url'],
            ],
        ]);
});

it('returns empty array when no active featured banners exist', function () {
    getJson('/api/v1/featured-banners')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

// ── Admin CRUD ───────────────────────────────────────────────────────────

it('requires auth for admin featured banner endpoints', function () {
    getJson('/api/v1/admin/featured-banners')->assertUnauthorized();
});

it('allows admin to create a featured banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create-featured-banner');
    Sanctum::actingAs($user);

    postJson('/api/v1/admin/featured-banners', [
        'title' => 'Featured',
        'sort_order' => 0,
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Featured');
});

it('allows admin to update a featured banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('update-featured-banner');
    Sanctum::actingAs($user);

    $banner = FeaturedBanner::factory()->create();

    putJson("/api/v1/admin/featured-banners/{$banner->id}", [
        'title' => 'Updated Featured',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated Featured');
});

it('allows admin to delete a featured banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete-featured-banner');
    Sanctum::actingAs($user);

    $banner = FeaturedBanner::factory()->create();

    deleteJson("/api/v1/admin/featured-banners/{$banner->id}")
        ->assertSuccessful();

    expect(FeaturedBanner::find($banner->id))->toBeNull();
});
