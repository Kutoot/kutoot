<?php

use App\Models\Campaign;
use App\Models\Stamp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can update their editable stamp code', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'APITST',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'user_id' => $user->id,
        'campaign_id' => $campaign->id,
        'code' => 'APITST-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/stamps/{$stamp->id}/code", [
            'slot_values' => [2, 8, 15],
        ]);

    $response->assertOk()
        ->assertJsonPath('stamp.code', 'APITST-02-08-15');
});

test('user cannot update another users stamp', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'OTHERTST',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'user_id' => $owner->id,
        'campaign_id' => $campaign->id,
        'code' => 'OTHERTST-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($other, 'sanctum')
        ->patchJson("/api/stamps/{$stamp->id}/code", [
            'slot_values' => [2, 8, 15],
        ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot update stamp code', function () {
    $stamp = Stamp::factory()->create([
        'editable_until' => now()->addMinutes(15),
    ]);

    $response = $this->patchJson("/api/stamps/{$stamp->id}/code", [
        'slot_values' => [1, 2, 3],
    ]);

    $response->assertUnauthorized();
});

test('update stamp code fails with expired edit window', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'EXPAPI',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'user_id' => $user->id,
        'campaign_id' => $campaign->id,
        'code' => 'EXPAPI-01-05-10',
        'editable_until' => now()->subMinutes(1),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/stamps/{$stamp->id}/code", [
            'slot_values' => [2, 8, 15],
        ]);

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Stamp edit window has expired.']);
});

test('update stamp code validates slot values are required', function () {
    $user = User::factory()->create();
    $stamp = Stamp::factory()->create([
        'user_id' => $user->id,
        'editable_until' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/stamps/{$stamp->id}/code", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slot_values']);
});
