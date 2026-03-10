<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStampCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $stamp = $this->route('stamp');

        return $stamp && $this->user() && $stamp->user_id === $this->user()->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'slot_values' => ['required', 'array'],
            'slot_values.*' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slot_values.required' => 'Please provide slot values for your stamp code.',
            'slot_values.array' => 'Slot values must be an array of numbers.',
            'slot_values.*.required' => 'Each slot must have a value.',
            'slot_values.*.integer' => 'Each slot value must be a whole number.',
        ];
    }
}
