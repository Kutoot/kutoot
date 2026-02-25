<?php

use App\Models\User;
use App\Services\OtpService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ── OTP Send ─────────────────────────────────────────────────────────────

it('sends OTP with a valid identifier', function () {
    $this->postJson('/api/v1/auth/otp/send', [
        'identifier' => 'test@example.com',
    ])->assertSuccessful()
        ->assertJsonStructure(['message']);
});

it('validates identifier is required for OTP send', function () {
    $this->postJson('/api/v1/auth/otp/send', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['identifier']);
});

it('creates a new user when sending OTP to unknown email', function () {
    $this->postJson('/api/v1/auth/otp/send', [
        'identifier' => 'newuser@example.com',
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('creates a new user when sending OTP to unknown mobile', function () {
    $this->postJson('/api/v1/auth/otp/send', [
        'identifier' => '9876543210',
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', ['mobile' => '9876543210']);
});

// ── OTP Verify ───────────────────────────────────────────────────────────

it('verifies OTP and returns a token', function () {
    $user = User::factory()->create(['email' => 'verify@example.com']);

    $otpService = app(OtpService::class);
    $otp = $otpService->generateOtp($user);

    $this->postJson('/api/v1/auth/otp/verify', [
        'identifier' => 'verify@example.com',
        'otp' => $otp,
    ])->assertSuccessful()
        ->assertJsonStructure(['token', 'user']);
});

it('fails OTP verification with wrong code', function () {
    $user = User::factory()->create(['email' => 'wrong@example.com']);

    $otpService = app(OtpService::class);
    $otpService->generateOtp($user);

    $this->postJson('/api/v1/auth/otp/verify', [
        'identifier' => 'wrong@example.com',
        'otp' => '000000',
    ])->assertUnprocessable();
});

it('validates required fields for OTP verify', function () {
    $this->postJson('/api/v1/auth/otp/verify', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['identifier', 'otp']);
});

// ── Authenticated User ───────────────────────────────────────────────────

it('returns the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/auth/user')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('returns 401 for unauthenticated user request', function () {
    $this->getJson('/api/v1/auth/user')
        ->assertUnauthorized();
});

// ── Logout ───────────────────────────────────────────────────────────────

it('logs out the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

it('returns 401 when logging out without authentication', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});
