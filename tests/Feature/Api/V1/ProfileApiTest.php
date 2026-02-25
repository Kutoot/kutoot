<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ── Show Profile ─────────────────────────────────────────────────────────

it('returns the authenticated user profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/profile')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('requires auth to view profile', function () {
    $this->getJson('/api/v1/profile')
        ->assertUnauthorized();
});

// ── Update Profile ───────────────────────────────────────────────────────

it('updates user profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ])->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('validates email uniqueness on profile update', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/profile', [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('allows keeping own email on profile update', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/profile', [
        'name' => 'Same Email',
        'email' => $user->email,
    ])->assertSuccessful();
});

// ── Delete Account ───────────────────────────────────────────────────────

it('deletes user account', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/profile')
        ->assertSuccessful();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
