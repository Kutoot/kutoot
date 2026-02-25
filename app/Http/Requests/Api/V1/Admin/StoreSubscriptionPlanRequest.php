<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-subscription-plan');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['sometimes', 'boolean'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'stamps_on_purchase' => ['required', 'integer', 'min:0'],
            'stamp_denomination' => ['required', 'numeric', 'min:0.01'],
            'stamps_per_denomination' => ['required', 'integer', 'min:0'],
            'max_discounted_bills' => ['required', 'integer', 'min:0'],
            'max_redeemable_amount' => ['required', 'numeric', 'min:0'],
            'campaigns' => ['sometimes', 'array'],
            'campaigns.*' => ['exists:campaigns,id'],
            'coupon_categories' => ['sometimes', 'array'],
            'coupon_categories.*' => ['exists:coupon_categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The plan name is required.',
            'price.required' => 'The plan price is required.',
            'stamps_on_purchase.required' => 'The stamps on purchase value is required.',
            'stamp_denomination.required' => 'The stamp denomination is required.',
            'stamp_denomination.min' => 'The stamp denomination must be at least ₹0.01.',
            'max_discounted_bills.required' => 'The maximum discounted bills is required.',
            'max_redeemable_amount.required' => 'The maximum redeemable amount is required.',
        ];
    }
}
