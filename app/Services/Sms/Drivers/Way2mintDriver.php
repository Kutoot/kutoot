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
            // We can use a simple replacement or standard url encoding, but standard might overkill.
            // Let's ensure we target the specific requirements.
            // Actually, `http_build_query` usually handles this, but let's see how we pass it.
            // The API expects parameters in the query string.

            // The 'text' parameter needs to be encoded.
            // If we use Http::get() with query params, Laravel (Guzzle) will url encode them automatically.
            // Let's rely on that first, but if we need manual HEX encoding for specific chars:
            // "The vertical bar (|) present in your message payload is considered an unsupported special character in raw API requests. To handle this it must be encoded into its HEX equivalent."

            // Important: We need to see if we should use `data4` param.
            // Email example: &data4=1701175557617315269,1702173216915572636
            // This looks like PE_ID,Template_ID.

            $templateId = $extra['template_id'] ?? $this->otpTemplateId;

            $data4 = [];
            if ($this->peId) {
                $data4[] = $this->peId;
            }
            if ($templateId) {
                $data4[] = $templateId;
            }
            $data4String = implode(',', $data4);

            $queryParams = [
                'username' => $this->username,
                'password' => $this->password,
                'to' => '91' . $to, // Assuming domestic India numbers, prefix 91 if not present?
                // Better to ensure the caller passes a clean number or we standardize.
                // For now, let's prepend 91 if it's a 10 digit number.
                'from' => $this->senderId,
                'text' => $message,
                'data4' => $data4String,
            ];

            // Clean number logic (basic)
            if (strlen($to) === 10) {
                $queryParams['to'] = '91' . $to;
            }
            else {
                $queryParams['to'] = $to;
            }

            // Manually encoding the pipe character if needed, but typically Guzzle handles it.
            // However, sometimes gateways are picky and want EXACTLY %7C, not just equivalent.
            // Guzzle encodes | as %7C by default in query params.

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelayMs)
                ->get($this->baseUrl . '/pushsms', $queryParams);

            if ($response->successful()) {
                Log::info("SMS Sent successfully via Way2mint", ['to' => $to, 'response' => $response->body()]);
                return true;
            }

            Log::error("Way2mint SMS Failed", ['to' => $to, 'status' => $response->status(), 'body' => $response->body()]);
            return false;

        }
        catch (\Exception $e) {
            Log::error("Way2mint SMS Exception", ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
