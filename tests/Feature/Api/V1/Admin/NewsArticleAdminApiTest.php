<?php

use App\Models\NewsArticle;
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

it('returns active news articles publicly without auth', function () {
    NewsArticle::factory()->count(4)->create(['is_active' => true]);
    NewsArticle::factory()->create(['is_active' => false]);

    getJson('/api/v1/news-articles')
        ->assertSuccessful()
        ->assertJsonCount(4, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'link_url', 'published_at', 'sort_order', 'is_active', 'image_url', 'thumb_url', 'preview_url'],
            ],
        ]);
});

it('returns empty array when no active news articles exist', function () {
    getJson('/api/v1/news-articles')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

// ── Admin CRUD ───────────────────────────────────────────────────────────

it('requires auth for admin news article endpoints', function () {
    getJson('/api/v1/admin/news-articles')->assertUnauthorized();
    postJson('/api/v1/admin/news-articles', [])->assertUnauthorized();
});

it('allows admin to create a news article', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create-news-article');
    Sanctum::actingAs($user);

    postJson('/api/v1/admin/news-articles', [
        'title' => 'Breaking News',
        'description' => 'Something happened.',
        'sort_order' => 0,
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Breaking News');
});

it('validates title is required for news articles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create-news-article');
    Sanctum::actingAs($user);

    postJson('/api/v1/admin/news-articles', [
        'description' => 'No title',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('allows admin to update a news article', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('update-news-article');
    Sanctum::actingAs($user);

    $article = NewsArticle::factory()->create(['title' => 'Old Title']);

    putJson("/api/v1/admin/news-articles/{$article->id}", [
        'title' => 'Updated Title',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated Title');
});

it('allows admin to delete a news article', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete-news-article');
    Sanctum::actingAs($user);

    $article = NewsArticle::factory()->create();

    deleteJson("/api/v1/admin/news-articles/{$article->id}")
        ->assertSuccessful();

    expect(NewsArticle::find($article->id))->toBeNull();
});

it('denies access without proper permissions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    getJson('/api/v1/admin/news-articles')->assertForbidden();
});
