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
            'labels.*.name' => 'required|string|max:255',
            'labels.*.color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'labels.*.description' => 'nullable|string|max:500',
            'labels.*.applicable_to' => 'required|array|min:1',
            'labels.*.applicable_to.*' => 'string|in:buildings,rooms,users,groups',
            'labels.*.is_active' => 'boolean',
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
            'labels.*.name.required' => 'Label name is required.',
            'labels.*.name.max' => 'Label name cannot exceed 255 characters.',
            'labels.*.color.required' => 'Label color is required.',
            'labels.*.color.regex' => 'Label color must be a valid hex color code (e.g., #FF0000).',
            'labels.*.description.max' => 'Label description cannot exceed 500 characters.',
            'labels.*.applicable_to.required' => 'At least one resource type must be selected.',
            'labels.*.applicable_to.min' => 'At least one resource type must be selected.',
            'labels.*.applicable_to.*.in' => 'Resource type must be one of: buildings, rooms, users, groups.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'labels.*.name' => 'label name',
            'labels.*.color' => 'label color',
            'labels.*.description' => 'label description',
            'labels.*.applicable_to' => 'applicable resources',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $labels = $this->input('labels', []);
            $labelNames = [];
            
            // Check for duplicate label names
            foreach ($labels as $index => $label) {
                if (isset($label['name'])) {
                    $name = strtolower(trim($label['name']));
                    if (in_array($name, $labelNames)) {
                        $validator->errors()->add("labels.{$index}.name", 'Duplicate label name. Each label must have a unique name.');
                    }
                    $labelNames[] = $name;
                }
            }
        });
    }
}