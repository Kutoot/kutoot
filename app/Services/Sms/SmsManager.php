<?php

namespace App\Services\Sms;

use App\Contracts\SmsContract;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\Drivers\Way2mintDriver;
use Illuminate\Support\Manager;

class SmsManager extends Manager implements SmsContract
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('services.sms.driver', 'log');
    }

    /**
     * Create an instance of the Log driver.
     *
     * @return \App\Services\Sms\Drivers\LogDriver
     */
    protected function createLogDriver()
    {
        return new LogDriver();
    }

    /**
     * Create an instance of the Way2mint driver.
     *
     * @return \App\Services\Sms\Drivers\Way2mintDriver
     */
    protected function createWay2mintDriver()
    {
        $config = $this->config->get('services.sms.way2mint');

        return new Way2mintDriver(
            $config['base_url'],
            $config['username'],
            $config['password'],
            $config['sender_id'],
            $config['pe_id'] ?? null,
            $config['otp_template_id'] ?? null,
            $config['timeout'] ?? 30,
            $config['retry_attempts'] ?? 3,
            $config['retry_delay_ms'] ?? 500
            );
    }

    /**
     * Send an SMS to the given phone number.
     *
     * @param  string  $to
     * @param  string  $message
     * @param  array  $extra
     * @return bool
     */
    public function send(string $to, string $message, array $extra = []): bool
    {
        return $this->driver()->send($to, $message, $extra);
    }
}
