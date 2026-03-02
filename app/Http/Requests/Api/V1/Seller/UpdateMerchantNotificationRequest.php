<?php

namespace App\Http\Requests\Api\V1\Seller;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'channels' => ['required', 'array'],
            'channels.email' => ['required', 'boolean'],
            'channels.sms' => ['required', 'boolean'],
            'channels.whatsapp' => ['required', 'boolean'],
        ];
    }
}
