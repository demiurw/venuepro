<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoomsRequest extends FormRequest
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
        $user = $this->user();
        
        return [
            'rooms' => 'required|array|min:1|max:20',
            'rooms.*.name' => 'required|string|max:255',
            'rooms.*.building_id' => [
                'required',
                'integer',
                Rule::exists('building', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'rooms.*.capacity' => 'required|integer|min:1|max:1000',
            'rooms.*.type' => 'required|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rooms.required' => 'At least one room is required.',
            'rooms.min' => 'At least one room must be provided.',
            'rooms.max' => 'Maximum 20 rooms can be created at once.',
            'rooms.*.name.required' => 'Room name is required.',
            'rooms.*.name.max' => 'Room name cannot exceed 255 characters.',
            'rooms.*.building_id.required' => 'Building selection is required.',
            'rooms.*.building_id.exists' => 'Selected building does not exist.',
            'rooms.*.capacity.required' => 'Room capacity is required.',
            'rooms.*.capacity.min' => 'Room capacity must be at least 1.',
            'rooms.*.capacity.max' => 'Room capacity cannot exceed 1000.',
            'rooms.*.type.required' => 'Room type is required.',
            'rooms.*.type.max' => 'Room type cannot exceed 100 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'rooms.*.name' => 'room name',
            'rooms.*.building_id' => 'building',
            'rooms.*.capacity' => 'room capacity',
            'rooms.*.type' => 'room type',
        ];
    }
}