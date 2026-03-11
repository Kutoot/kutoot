<?php

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsContract;
use Illuminate\Support\Facades\Log;

class LogDriver implements SmsContract
{
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
        Log::info("SMS Sent to {$to}: {$message}", $extra);

        return true;
    }
}
