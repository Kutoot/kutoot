<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\TargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMerchantLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-merchant-location');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'merchant_id' => ['required', 'exists:merchants,id'],
            'branch_name' => ['required', 'string', 'max:255'],
            'commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'monthly_target_type' => ['nullable', Rule::enum(TargetType::class)],
            'monthly_target_value' => ['nullable', 'numeric', 'min:0.01'],
            'deduct_commission_from_target' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'merchant_id.required' => 'A merchant is required.',
            'merchant_id.exists' => 'The selected merchant is invalid.',
            'branch_name.required' => 'The branch name is required.',
            'commission_percentage.required' => 'The commission percentage is required.',
        ];
    }
}
