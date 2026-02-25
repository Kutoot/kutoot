<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Super Admin');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'min_streak_months' => ['required', 'integer', 'min:3'],
            'max_loan_amount' => ['required', 'numeric', 'min:1'],
            'interest_rate_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'min_streak_months.required' => 'The minimum streak months is required.',
            'min_streak_months.min' => 'The minimum streak must be at least 3 months.',
            'max_loan_amount.required' => 'The maximum loan amount is required.',
            'max_loan_amount.min' => 'The maximum loan amount must be at least ₹1.',
        ];
    }
}
