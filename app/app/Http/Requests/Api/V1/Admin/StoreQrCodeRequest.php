<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\QrCodeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-qr-code');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'unique_code' => ['required', 'string', 'max:255', 'unique:qr_codes,unique_code'],
            'token' => ['required', 'string', 'max:255', 'unique:qr_codes,token'],
            'merchant_location_id' => ['nullable', 'exists:merchant_locations,id'],
            'status' => ['required', Rule::enum(QrCodeStatus::class)],
            'linked_at' => ['nullable', 'date'],
            'linked_by' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unique_code.required' => 'The unique code is required.',
            'unique_code.unique' => 'This unique code is already in use.',
            'token.required' => 'The token is required.',
            'token.unique' => 'This token is already in use.',
        ];
    }
}
