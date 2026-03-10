<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\CampaignStatus;
use App\Enums\CreatorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-campaign');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:campaign_categories,id'],
            'creator_type' => ['required', Rule::enum(CreatorType::class)],
            'creator_id' => ['required', 'exists:users,id'],
            'reward_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(CampaignStatus::class)],
            'start_date' => ['required', 'date'],
            'reward_cost_target' => ['required', 'numeric', 'min:0'],
            'stamp_target' => ['required', 'numeric', 'min:0'],
            'collected_commission_cache' => ['sometimes', 'numeric', 'min:0'],
            'issued_stamps_cache' => ['sometimes', 'integer', 'min:0'],
            'marketing_bounty_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'winner_announcement_date' => ['nullable', 'date'],
            'plans' => ['sometimes', 'array'],
            'plans.*' => ['exists:subscription_plans,id'],
            'code' => ['nullable', 'string', 'max:20', 'unique:campaigns,code'],
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
            'category_id.required' => 'A campaign category is required.',
            'category_id.exists' => 'The selected campaign category is invalid.',
            'creator_type.required' => 'The creator type is required.',
            'creator_id.required' => 'A creator is required.',
            'reward_name.required' => 'The reward name is required.',
            'status.required' => 'The campaign status is required.',
            'start_date.required' => 'A start date is required.',
            'reward_cost_target.required' => 'The reward cost target is required.',
            'stamp_target.required' => 'The stamp target is required.',
            'code.unique' => 'This campaign code is already in use.',
            'marketing_bounty_percentage.max' => 'The marketing bounty percentage cannot exceed 100.',
        ];
    }
}
