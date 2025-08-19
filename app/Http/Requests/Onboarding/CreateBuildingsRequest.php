<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBuildingsRequest extends FormRequest
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
            'buildings' => 'required|array|min:1|max:10',
            'buildings.*.name' => 'required|string|max:255',
            'buildings.*.address' => 'required|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'buildings.required' => 'At least one building is required.',
            'buildings.min' => 'At least one building must be provided.',
            'buildings.max' => 'Maximum 10 buildings can be created at once.',
            'buildings.*.name.required' => 'Building name is required.',
            'buildings.*.name.max' => 'Building name cannot exceed 255 characters.',
            'buildings.*.address.required' => 'Building address is required.',
            'buildings.*.address.max' => 'Building address cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'buildings.*.name' => 'building name',
            'buildings.*.address' => 'building address',
        ];
    }
}