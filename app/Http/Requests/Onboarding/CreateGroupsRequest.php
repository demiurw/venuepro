<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupsRequest extends FormRequest
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
            'groups' => 'required|array|min:1|max:15',
            'groups.*.name' => 'required|string|max:255',
            'groups.*.description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'groups.required' => 'At least one group is required.',
            'groups.min' => 'At least one group must be provided.',
            'groups.max' => 'Maximum 15 groups can be created at once.',
            'groups.*.name.required' => 'Group name is required.',
            'groups.*.name.max' => 'Group name cannot exceed 255 characters.',
            'groups.*.description.max' => 'Group description cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'groups.*.name' => 'group name',
            'groups.*.description' => 'group description',
        ];
    }
}