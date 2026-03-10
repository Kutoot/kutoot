<?php

namespace App\Contracts;

interface SmsContract
{
    /**
     * Send an SMS to the given phone number.
     *
     * @param  string  $to
     * @param  string  $message
     * @param  array  $extra
     * @return bool
     */
    public function send(string $to, string $message, array $extra = []): bool;
}
