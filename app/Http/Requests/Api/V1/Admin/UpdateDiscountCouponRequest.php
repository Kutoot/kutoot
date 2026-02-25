<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-discount-coupon');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'coupon_category_id' => ['sometimes', 'exists:coupon_categories,id'],
            'merchant_location_id' => ['nullable', 'exists:merchant_locations,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['sometimes', Rule::enum(DiscountType::class)],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'code' => ['sometimes', 'string', 'max:255'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_user' => ['sometimes', 'integer', 'min:1'],
            'starts_at' => ['sometimes', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
