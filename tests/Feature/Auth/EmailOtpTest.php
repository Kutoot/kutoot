<?php

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Mail;

test('email otp sends mail to user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'emailotp@example.com']);

    $otpService = app(OtpService::class);
    $otp = $otpService->generateOtp($user);
    $otpService->sendOtp($user, $otp, 'email');

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->otp !== '';
    });
});

test('email otp contains correct code', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'code@example.com']);

    $otpService = app(OtpService::class);
    $otp = $otpService->generateOtp($user);
    $otpService->sendOtp($user, $otp, 'email');

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) use ($otp) {
        return $mail->otp === $otp;
    });
});

test('mobile otp does not send email', function () {
    Mail::fake();

    $user = User::factory()->create(['mobile' => '9000000001']);

    $otpService = app(OtpService::class);
    $otp = $otpService->generateOtp($user);
    $otpService->sendOtp($user, $otp, 'mobile');

    Mail::assertNothingSent();
});

test('otp login flow sends email when identifier is email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'loginflow@example.com']);

    $response = $this->post('/otp-login/send', [
        'identifier' => 'loginflow@example.com',
    ]);

    $response->assertRedirect();

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        return $mail->hasTo('loginflow@example.com');
    });
});

test('otp login flow auto-creates user and sends email', function () {
    Mail::fake();

    $response = $this->post('/otp-login/send', [
        'identifier' => 'newuser@example.com',
    ]);

    $response->assertRedirect();

    // User should be auto-created
    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        return $mail->hasTo('newuser@example.com');
    });
});

test('otp verification works after email otp is sent', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'verify@example.com']);

    $otpService = app(OtpService::class);
    $otp = $otpService->generateOtp($user);

    $response = $this->post('/otp-login/verify', [
        'identifier' => 'verify@example.com',
        'otp' => $otp,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
