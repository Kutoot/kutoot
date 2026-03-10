<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-permissions');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($this->route('permission'))],
            'guard_name' => ['sometimes', 'string', Rule::in(['web', 'api'])],
        ];
    }
}
