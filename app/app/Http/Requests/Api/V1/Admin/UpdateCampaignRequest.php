<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\CampaignStatus;
use App\Enums\CreatorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-campaign');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'exists:campaign_categories,id'],
            'creator_type' => ['sometimes', Rule::enum(CreatorType::class)],
            'creator_id' => ['sometimes', 'exists:users,id'],
            'reward_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::enum(CampaignStatus::class)],
            'start_date' => ['sometimes', 'date'],
            'reward_cost_target' => ['sometimes', 'numeric', 'min:0'],
            'stamp_target' => ['sometimes', 'numeric', 'min:0'],
            'collected_commission_cache' => ['sometimes', 'numeric', 'min:0'],
            'issued_stamps_cache' => ['sometimes', 'integer', 'min:0'],
            'marketing_bounty_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'winner_announcement_date' => ['nullable', 'date'],
            'plans' => ['sometimes', 'array'],
            'plans.*' => ['exists:subscription_plans,id'],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('campaigns', 'code')->ignore($this->route('campaign'))],
            'stamp_slots' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'stamp_slot_min' => ['sometimes', 'integer', 'min:0'],
            'stamp_slot_max' => ['sometimes', 'integer', 'min:1'],
            'stamp_editable_on_plan_purchase' => ['sometimes', 'boolean'],
            'stamp_editable_on_coupon_redemption' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected campaign category is invalid.',
            'code.unique' => 'This campaign code is already in use.',
            'marketing_bounty_percentage.max' => 'The marketing bounty percentage cannot exceed 100.',
        ];
    }
}
