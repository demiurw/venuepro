<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUsersRequest extends FormRequest
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
        $allowedRoles = ['booking_agent', 'hod', 'invitee'];
        
        return [
            'users' => 'required|array|min:1|max:25',
            'users.*.first_name' => 'required|string|max:100',
            'users.*.last_name' => 'required|string|max:100',
            'users.*.email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'users.*.user_type' => ['required', 'string', Rule::in($allowedRoles)],
            'users.*.group_id' => [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'users.required' => 'At least one user is required.',
            'users.min' => 'At least one user must be provided.',
            'users.max' => 'Maximum 25 users can be created at once.',
            'users.*.first_name.required' => 'First name is required.',
            'users.*.first_name.max' => 'First name cannot exceed 100 characters.',
            'users.*.last_name.required' => 'Last name is required.',
            'users.*.last_name.max' => 'Last name cannot exceed 100 characters.',
            'users.*.email.required' => 'Email is required.',
            'users.*.email.email' => 'Email must be a valid email address.',
            'users.*.email.max' => 'Email cannot exceed 191 characters.',
            'users.*.email.unique' => 'This email is already taken.',
            'users.*.user_type.required' => 'User type is required.',
            'users.*.user_type.in' => 'User type must be one of: booking_agent, hod, or invitee.',
            'users.*.group_id.exists' => 'Selected group does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'users.*.first_name' => 'first name',
            'users.*.last_name' => 'last name',
            'users.*.email' => 'email',
            'users.*.user_type' => 'user type',
            'users.*.group_id' => 'group',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $users = $this->input('users', []);
            $emails = [];
            
            // Check for duplicate emails within the request
            foreach ($users as $index => $user) {
                if (isset($user['email'])) {
                    if (in_array($user['email'], $emails)) {
                        $validator->errors()->add("users.{$index}.email", 'Duplicate email addresses are not allowed.');
                    }
                    $emails[] = $user['email'];
                }
            }

            // Validate group_id requirement for certain user types
            foreach ($users as $index => $user) {
                if (isset($user['user_type']) && in_array($user['user_type'], ['booking_agent', 'hod'])) {
                    if (empty($user['group_id'])) {
                        $validator->errors()->add("users.{$index}.group_id", 'Group is required for ' . $user['user_type'] . ' users.');
                    }
                }
            }
        });
    }
}