<?php

use App\Enums\QrCodeStatus;
use App\Models\MerchantLocation;
use App\Models\QrCode;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('scans a linked QR code and returns merchant location', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $location = MerchantLocation::factory()->create();
    $qrCode = QrCode::factory()->create([
        'status' => QrCodeStatus::Linked,
        'merchant_location_id' => $location->id,
    ]);

    $this->getJson('/api/v1/qr/'.$qrCode->token.'/scan')
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['qr_code', 'merchant_location']]);
});

it('returns error for unlinked QR code', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $qrCode = QrCode::factory()->create([
        'status' => QrCodeStatus::Available,
    ]);

    $this->getJson('/api/v1/qr/'.$qrCode->token.'/scan')
        ->assertUnprocessable();
});

it('returns 404 for non-existent QR token', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/qr/non-existent-token/scan')
        ->assertNotFound();
});

it('requires auth for QR scan', function () {
    $qrCode = QrCode::factory()->create();

    $this->getJson('/api/v1/qr/'.$qrCode->token.'/scan')
        ->assertUnauthorized();
});
