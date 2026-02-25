<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('returns dashboard data for authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/dashboard')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'user',
                'plan',
                'stats',
            ],
        ]);
});

it('requires auth for dashboard', function () {
    $this->getJson('/api/v1/dashboard')
        ->assertUnauthorized();
});
