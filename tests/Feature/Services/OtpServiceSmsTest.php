<?php

namespace Tests\Feature\Services;

use App\Contracts\SmsContract;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class OtpServiceSmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_otp_sends_sms_when_channel_is_mobile()
    {
        $smsMock = Mockery::mock(SmsContract::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->withArgs(function ($to, $message) {
            return $to === '9876543210' &&
            str_contains($message, 'Your Kutoot login OTP is:') &&
            str_contains($message, 'Team Kutoot | Shopping is Winning');
        });

        $this->app->instance(SmsContract::class , $smsMock);

        $service = $this->app->make(OtpService::class);
        $user = User::factory()->create(['mobile' => '9876543210']);
        $otp = '123456';

        $service->sendOtp($user, $otp, 'mobile');
    }

    public function test_send_otp_sends_sms_when_identifier_is_provided_and_channel_is_mobile()
    {
        $smsMock = Mockery::mock(SmsContract::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with('9876543210', Mockery::pattern('/Your Kutoot login OTP is: \d+/'));

        $this->app->instance(SmsContract::class , $smsMock);

        $service = $this->app->make(OtpService::class);
        $otp = '123456';

        $service->sendOtp(null, $otp, 'mobile', '9876543210');
    }
}
