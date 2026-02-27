<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-news-article');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'link_url.url' => 'The link URL must be a valid URL.',
            'image.max' => 'The image must not be larger than 2MB.',
            'image.mimes' => 'The image must be a JPEG, PNG, or WebP file.',
            'description.max' => 'The description must not exceed 5000 characters.',
        ];
    }
}
