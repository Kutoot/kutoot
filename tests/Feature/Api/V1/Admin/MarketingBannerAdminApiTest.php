<?php

use App\Models\MarketingBanner;
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

it('returns active marketing banners publicly without auth', function () {
    MarketingBanner::factory()->count(3)->create(['is_active' => true, 'sort_order' => 1]);
    MarketingBanner::factory()->create(['is_active' => false, 'sort_order' => 0]);

    getJson('/api/v1/marketing-banners')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'subtitle', 'link_url', 'link_text', 'sort_order', 'is_active', 'image_url', 'thumb_url', 'preview_url'],
            ],
        ]);
});

it('returns empty array when no active marketing banners exist', function () {
    getJson('/api/v1/marketing-banners')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('orders marketing banners by sort_order then created_at desc', function () {
    $second = MarketingBanner::factory()->create(['sort_order' => 2, 'title' => 'Second']);
    $first = MarketingBanner::factory()->create(['sort_order' => 1, 'title' => 'First']);

    $response = getJson('/api/v1/marketing-banners')->assertSuccessful();

    expect($response->json('data.0.title'))->toBe('First');
    expect($response->json('data.1.title'))->toBe('Second');
});

// ── Admin CRUD ───────────────────────────────────────────────────────────

it('requires auth for admin marketing banner endpoints', function () {
    getJson('/api/v1/admin/marketing-banners')->assertUnauthorized();
    postJson('/api/v1/admin/marketing-banners', [])->assertUnauthorized();
});

it('allows admin to list marketing banners', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view-any-marketing-banner');
    Sanctum::actingAs($user);

    MarketingBanner::factory()->count(3)->create();

    getJson('/api/v1/admin/marketing-banners')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('allows admin to create a marketing banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create-marketing-banner');
    Sanctum::actingAs($user);

    postJson('/api/v1/admin/marketing-banners', [
        'title' => 'Test Banner',
        'subtitle' => 'Test Subtitle',
        'sort_order' => 1,
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Test Banner');
});

it('allows admin to update a marketing banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('update-marketing-banner');
    Sanctum::actingAs($user);

    $banner = MarketingBanner::factory()->create(['title' => 'Old Title']);

    putJson("/api/v1/admin/marketing-banners/{$banner->id}", [
        'title' => 'New Title',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'New Title');
});

it('allows admin to delete a marketing banner', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete-marketing-banner');
    Sanctum::actingAs($user);

    $banner = MarketingBanner::factory()->create();

    deleteJson("/api/v1/admin/marketing-banners/{$banner->id}")
        ->assertSuccessful()
        ->assertJsonPath('message', 'Marketing banner deleted.');

    expect(MarketingBanner::find($banner->id))->toBeNull();
});

it('denies access without proper permissions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    getJson('/api/v1/admin/marketing-banners')->assertForbidden();
});
