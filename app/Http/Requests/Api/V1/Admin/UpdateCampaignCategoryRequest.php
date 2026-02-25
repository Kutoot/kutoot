<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-campaign-category');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('campaign_categories', 'slug')->ignore($this->route('campaign_category'))],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
