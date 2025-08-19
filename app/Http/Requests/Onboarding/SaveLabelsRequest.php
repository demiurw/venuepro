<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLabelsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_type === 'system_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'labels' => 'required|array|min:1|max:20',
            'labels.*.type' => [
                'required',
                'string',
                Rule::in(['department', 'division', 'team', 'category', 'location', 'custom'])
            ],
            'labels.*.value' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'labels.required' => 'At least one label is required.',
            'labels.min' => 'At least one label must be provided.',
            'labels.max' => 'Maximum 20 labels can be saved at once.',
            'labels.*.type.required' => 'Label type is required.',
            'labels.*.type.in' => 'Label type must be one of: department, division, team, category, location, or custom.',
            'labels.*.value.required' => 'Label value is required.',
            'labels.*.value.max' => 'Label value cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'labels.*.type' => 'label type',
            'labels.*.value' => 'label value',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $labels = $this->input('labels', []);
            $labelCombinations = [];
            
            // Check for duplicate type-value combinations
            foreach ($labels as $index => $label) {
                if (isset($label['type']) && isset($label['value'])) {
                    $combination = $label['type'] . '|' . $label['value'];
                    if (in_array($combination, $labelCombinations)) {
                        $validator->errors()->add("labels.{$index}", 'Duplicate label type and value combination.');
                    }
                    $labelCombinations[] = $combination;
                }
            }
        });
    }
}