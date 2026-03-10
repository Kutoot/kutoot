<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-campaign-category');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:campaign_categories,slug'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
