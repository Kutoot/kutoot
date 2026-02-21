<?php

use App\Models\User;

test("new mobile users are automatically registered and logged in via OTP", function () {
    $mobile = "9876543210";

    // Step 1: Send OTP for new mobile
    $response = $this->post("/otp-login/send", [
        "identifier" => $mobile,
    ]);

    $response->assertSessionHas("status");
    $this->assertDatabaseHas("users", [
        "mobile" => $mobile,
    ]);

    $user = User::where("mobile", $mobile)->first();
    expect($user->name)->toBe("User " . $mobile);
    $otp = $user->otp_code;
    expect($otp)->not->toBeNull();

    // Step 2: Verify OTP
    $response = $this->post("/otp-login/verify", [
        "identifier" => $mobile,
        "otp" => $otp,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route("dashboard", absolute: false));
});

test("new email users are automatically registered and logged in via OTP", function () {
    $email = "otpuser@example.com";

    // Step 1: Send OTP for new email
    $response = $this->post("/otp-login/send", [
        "identifier" => $email,
    ]);

    $response->assertSessionHas("status");
    $this->assertDatabaseHas("users", [
        "email" => $email,
    ]);

    $user = User::where("email", $email)->first();
    expect($user->name)->toBe("otpuser");
    $otp = $user->otp_code;

    // Step 2: Verify OTP
    $response = $this->post("/otp-login/verify", [
        "identifier" => $email,
        "otp" => $otp,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route("dashboard", absolute: false));
});

test("existing users can login via OTP", function () {
    $user = User::factory()->create([
        "mobile" => "1234567890",
    ]);

    // Step 1: Send OTP
    $this->post("/otp-login/send", [
        "identifier" => $user->mobile,
    ]);

    $user->refresh();
    $otp = $user->otp_code;

    // Step 2: Verify OTP
    $response = $this->post("/otp-login/verify", [
        "identifier" => $user->mobile,
        "otp" => $otp,
    ]);

    $this->assertAuthenticatedAs($user);
});
