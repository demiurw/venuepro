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
            'buildings.*.description' => 'nullable|string|max:1000',
            'buildings.*.address_line1' => 'required|string|max:191',
            'buildings.*.address_line2' => 'nullable|string|max:191',
            'buildings.*.city' => 'required|string|max:100',
            'buildings.*.state_id' => 'nullable|exists:states,id',
            'buildings.*.country_id' => 'required|exists:countries,id',
            'buildings.*.postal_code' => 'nullable|string|max:20',
            'buildings.*.timezone' => 'nullable|string|max:50',
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
            'buildings.*.address_line1.required' => 'Street address is required.',
            'buildings.*.address_line1.max' => 'Street address cannot exceed 191 characters.',
            'buildings.*.city.required' => 'City is required.',
            'buildings.*.city.max' => 'City name cannot exceed 100 characters.',
            'buildings.*.country_id.required' => 'Country selection is required.',
            'buildings.*.country_id.exists' => 'Selected country is invalid.',
            'buildings.*.state_id.exists' => 'Selected state is invalid.',
            'buildings.*.postal_code.max' => 'Postal code cannot exceed 20 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'buildings.*.name' => 'building name',
            'buildings.*.address_line1' => 'street address',
            'buildings.*.address_line2' => 'address line 2',
            'buildings.*.city' => 'city',
            'buildings.*.state_id' => 'state',
            'buildings.*.country_id' => 'country',
            'buildings.*.postal_code' => 'postal code',
            'buildings.*.timezone' => 'timezone',
        ];
    }
}