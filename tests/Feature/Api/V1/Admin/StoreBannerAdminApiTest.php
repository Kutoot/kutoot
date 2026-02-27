<?php

use App\Models\StoreBanner;
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

it('returns active store banners publicly without auth', function () {
    StoreBanner::factory()->count(3)->create(['is_active' => true]);
    StoreBanner::factory()->create(['is_active' => false]);

    getJson('/api/v1/store-banners')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'alt_text', 'link_url', 'sort_order', 'is_active', 'image_url', 'thumb_url', 'preview_url'],
            ],
        ]);
});

it('returns empty array when no active store banners exist', function () {
    getJson('/api/v1/store-banners')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

// ── Admin CRUD ───────────────────────────────────────────────────────────

it('requires auth for admin store banner endpoints', function () {
    getJson('/api/v1/admin/store-banners')->assertUnauthorized();
});

it('allows admin to create a store banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create-store-banner');
    Sanctum::actingAs($user);

    postJson('/api/v1/admin/store-banners', [
        'title' => 'Store Banner',
        'alt_text' => 'Alt text',
        'sort_order' => 1,
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Store Banner');
});

it('allows admin to update a store banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('update-store-banner');
    Sanctum::actingAs($user);

    $banner = StoreBanner::factory()->create(['title' => 'Old']);

    putJson("/api/v1/admin/store-banners/{$banner->id}", [
        'title' => 'Updated',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated');
});

it('allows admin to delete a store banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete-store-banner');
    Sanctum::actingAs($user);

    $banner = StoreBanner::factory()->create();

    deleteJson("/api/v1/admin/store-banners/{$banner->id}")
        ->assertSuccessful();

    expect(StoreBanner::find($banner->id))->toBeNull();
});
