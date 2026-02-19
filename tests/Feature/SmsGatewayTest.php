<?php

namespace Tests\Feature;

use App\Contracts\SmsContract;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\Drivers\Way2mintDriver;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsGatewayTest extends TestCase
{
    public function test_it_resolves_log_driver_by_default()
    {
        Config::set('services.sms.driver', 'log');

        $manager = app(SmsManager::class);
        $this->assertInstanceOf(LogDriver::class , $manager->driver());
    }

    public function test_it_resolves_way2mint_driver()
    {
        Config::set('services.sms.driver', 'way2mint');
        Config::set('services.sms.way2mint', [
            'base_url' => 'https://test.com',
            'username' => 'user',
            'password' => 'pass',
            'sender_id' => 'TEST',
            'pe_id' => '123',
            'otp_template_id' => '456',
        ]);

        $manager = app(SmsManager::class);
        $this->assertInstanceOf(Way2mintDriver::class , $manager->driver());
    }

    public function test_log_driver_logs_message()
    {
        Log::shouldReceive('info')
            ->once()
            ->with("SMS Sent to 1234567890: Test Message", []);

        $driver = new LogDriver();
        $result = $driver->send('1234567890', 'Test Message');

        $this->assertTrue($result);
    }

    public function test_way2mint_driver_sends_correct_request()
    {
        Http::fake([
            'test.com/*' => Http::response('success', 200),
        ]);

        $driver = new Way2mintDriver(
            'https://test.com',
            'user',
            'pass',
            'SENDER',
            '1001', // PE ID
            '2002' // Template ID
            );

        $result = $driver->send('9876543210', 'Your OTP is 1234 | Team Kutoot');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://test.com/pushsms?username=user&password=pass&to=919876543210&from=SENDER&text=Your%20OTP%20is%201234%20%7C%20Team%20Kutoot&data4=1001%2C2002';
        });
    }

    public function test_way2mint_driver_uses_provided_template_id()
    {
        Http::fake([
            'test.com/*' => Http::response('success', 200),
        ]);

        $driver = new Way2mintDriver(
            'https://test.com',
            'user',
            'pass',
            'SENDER',
            '1001', // PE ID
            'DEFAULT_TEMPLATE' // Default Template ID
            );

        $customTemplateId = 'CUSTOM_TEMPLATE_123';
        $result = $driver->send('9876543210', 'Custom Message', ['template_id' => $customTemplateId]);

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($customTemplateId) {
            return str_contains($request->url(), "data4=1001%2C{$customTemplateId}");
        });
    }

    public function test_way2mint_driver_handles_failure()
    {
        Http::fake([
            'test.com/*' => Http::response('error', 500),
        ]);

        Log::shouldReceive('error')->once();

        $driver = new Way2mintDriver(
            'https://test.com',
            'user',
            'pass',
            'SENDER'
            );

        $result = $driver->send('9876543210', 'Test');

        $this->assertFalse($result);
    }
}
