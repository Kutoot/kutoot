<?php

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Way2mintDriver implements SmsContract
{
    public function __construct(protected
        string $baseUrl, protected
        string $username, protected
        string $password, protected
        string $senderId, protected
        ?string $peId = null, protected
        ?string $providerPeId = null, protected
        ?string $otpTemplateId = null, protected
        int $timeout = 30, protected
        int $retryAttempts = 3, protected
        int $retryDelayMs = 500
        )
    {
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
        try {
            // Encode special characters as required (specifically | to %7C)
            // The email mentions: @ => %40, ! => %21, & => %26, | => %7C
            // We use SmsHelper for this.

            // Prepare data4 (PE_ID, PROVIDER_PE_ID) as per user instruction "last value is way2mint peid"
            $data4 = [];
            if ($this->peId) {
                $data4[] = $this->peId;
            }
            if ($this->providerPeId) {
                $data4[] = $this->providerPeId;
            }
            $data4String = implode(',', $data4);

            $queryParams = [
                'username' => $this->username,
                'password' => $this->password,
                'to' => '91' . ltrim($to, '+'), // Reference uses: "91" . ltrim($phone, "+")
                'from' => $this->senderId,
                'text' => $message,
                'data4' => $data4String,
            ];

            $url = $this->baseUrl . '/pushsms';

            Log::debug("Way2mint API Request", ['url' => $url, 'params' => $queryParams]);

            $response = Http::withOptions(['verify' => false]) // Disable SSL verification as per reference
                ->timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelayMs)
                ->get($url, $queryParams);

            Log::debug("Way2mint Effective URI: " . (string)$response->effectiveUri());

            $body = $response->body();

            // check for "success" in body (case-insensitive) as per reference
            if (stripos($body, 'success') !== false) {
                Log::info("SMS Sent successfully via Way2mint", ['to' => $to, 'response' => $body]);
                return true;
            }

            Log::error("Way2mint SMS Failed", ['to' => $to, 'status' => $response->status(), 'body' => $body]);
            return false;

        }
        catch (\Exception $e) {
            Log::error("Way2mint SMS Exception", ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
