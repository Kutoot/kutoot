<?php

namespace App\Http\Requests\Api\V1\Seller;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantLocationBankRequest extends FormRequest
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
            'bank_name' => ['sometimes', 'string', 'max:255'],
            'sub_bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'account_number' => ['sometimes', 'string', 'max:30'],
            'ifsc_code' => ['sometimes', 'string', 'max:20'],
            'upi_id' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
