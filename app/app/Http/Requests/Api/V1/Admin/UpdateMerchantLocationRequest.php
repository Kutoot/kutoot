<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\TargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMerchantLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-merchant-location');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'merchant_id' => ['sometimes', 'exists:merchants,id'],
            'branch_name' => ['sometimes', 'string', 'max:255'],
            'commission_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'monthly_target_type' => ['nullable', Rule::enum(TargetType::class)],
            'monthly_target_value' => ['nullable', 'numeric', 'min:0.01'],
            'deduct_commission_from_target' => ['sometimes', 'boolean'],
        ];
    }
}
