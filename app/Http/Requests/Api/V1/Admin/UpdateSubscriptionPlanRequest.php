<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-subscription-plan');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_default' => ['sometimes', 'boolean'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'stamps_on_purchase' => ['sometimes', 'integer', 'min:0'],
            'stamp_denomination' => ['sometimes', 'numeric', 'min:0.01'],
            'stamps_per_denomination' => ['sometimes', 'integer', 'min:0'],
            'max_discounted_bills' => ['sometimes', 'integer', 'min:0'],
            'max_redeemable_amount' => ['sometimes', 'numeric', 'min:0'],
            'campaigns' => ['sometimes', 'array'],
            'campaigns.*' => ['exists:campaigns,id'],
            'coupon_categories' => ['sometimes', 'array'],
            'coupon_categories.*' => ['exists:coupon_categories,id'],
        ];
    }
}
