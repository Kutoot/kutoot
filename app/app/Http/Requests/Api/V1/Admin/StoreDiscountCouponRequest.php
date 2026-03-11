<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-discount-coupon');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'coupon_category_id' => ['required', 'exists:coupon_categories,id'],
            'merchant_location_id' => ['nullable', 'exists:merchant_locations,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'code' => ['required', 'string', 'max:255'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_user' => ['required', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'coupon_category_id.required' => 'A coupon category is required.',
            'title.required' => 'The coupon title is required.',
            'discount_type.required' => 'The discount type is required.',
            'discount_value.required' => 'The discount value is required.',
            'code.required' => 'A coupon code is required.',
            'usage_per_user.required' => 'The per-user usage limit is required.',
            'starts_at.required' => 'A start date is required.',
            'expires_at.after' => 'The expiry date must be after the start date.',
        ];
    }
}
