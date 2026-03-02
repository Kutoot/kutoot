<?php

namespace App\Http\Requests\Api\V1\Seller;

use Illuminate\Foundation\Http\FormRequest;

class ChangeMerchantPasswordRequest extends FormRequest
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
            'oldPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:6', 'max:100'],
        ];
    }
}
