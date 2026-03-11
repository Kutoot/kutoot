<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\QrCodeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-qr-code');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'unique_code' => ['sometimes', 'string', 'max:255', Rule::unique('qr_codes', 'unique_code')->ignore($this->route('qr_code'))],
            'token' => ['sometimes', 'string', 'max:255', Rule::unique('qr_codes', 'token')->ignore($this->route('qr_code'))],
            'merchant_location_id' => ['nullable', 'exists:merchant_locations,id'],
            'status' => ['sometimes', Rule::enum(QrCodeStatus::class)],
            'linked_at' => ['nullable', 'date'],
            'linked_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
