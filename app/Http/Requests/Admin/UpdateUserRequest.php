<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Models\Group;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $targetUser = $this->route('companyUser');
        
        // Only authenticated users can update users
        if (!$user) {
            return false;
        }
        
        // System admin can update users in their company
        if ($user->user_type === 'system_admin') {
            return $targetUser && $targetUser->company_id === $user->company_id;
        }
        
        // HOD can update users in their group (excluding other HODs and system_admin)
        if ($user->user_type === 'hod' && $user->group_id) {
            return $targetUser && 
                   $targetUser->company_id === $user->company_id &&
                   $targetUser->group_id === $user->group_id &&
                   in_array($targetUser->user_type, ['booking_agent', 'invitee']);
        }
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = auth()->user();
        $targetUser = $this->route('companyUser');
        
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })->ignore($targetUser?->id)
            ],
        ];

        // Role validation based on who is making the update
        if ($user->user_type === 'system_admin') {
            $allowedRoles = ['booking_agent', 'invitee', 'hod'];
            $rules['user_type'] = ['required', 'string', Rule::in($allowedRoles)];
            
            // Status can be updated by system_admin
            $rules['status'] = ['sometimes', 'string', Rule::in(UserStatus::values())];
        } elseif ($user->user_type === 'hod') {
            // HOD can only manage booking_agent and invitee roles
            $allowedRoles = ['booking_agent', 'invitee'];
            $rules['user_type'] = ['required', 'string', Rule::in($allowedRoles)];
            
            // HOD cannot change status - only system_admin can
            // The existing status will be preserved
        }

        // Group validation - depends on role requirements and user permissions
        if ($user->user_type === 'system_admin') {
            // System admin can assign any group in their company
            $this->addGroupValidation($rules, $user, $this->input('user_type'));
        } elseif ($user->user_type === 'hod') {
            // HOD can only assign users to their own group
            $rules['group_id'] = [
                'required',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('id', $user->group_id)
                                 ->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ];
        }

        return $rules;
    }

    /**
     * Add group validation rules based on role requirements.
     */
    protected function addGroupValidation(array &$rules, User $user, ?string $userType): void
    {
        if (in_array($userType, ['booking_agent', 'hod'])) {
            $rules['group_id'] = [
                'required',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ];
        } else {
            // Optional for other roles (like invitee)
            $rules['group_id'] = [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ];
        }
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name cannot exceed 100 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name cannot exceed 100 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use by another user in your company.',
            'user_type.required' => 'User role is required.',
            'user_type.in' => 'Selected user role is not valid.',
            'status.in' => 'Selected status is not valid.',
            'group_id.required' => 'Group assignment is required for this role.',
            'group_id.exists' => 'Selected group does not exist or is not active.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            $targetUser = $this->route('companyUser');
            
            // Prevent privilege escalation
            if ($user->user_type === 'hod') {
                $requestedRole = $this->input('user_type');
                
                // HOD cannot elevate users to HOD or system_admin
                if (in_array($requestedRole, ['hod', 'system_admin'])) {
                    $validator->errors()->add('user_type', 'You cannot assign this role.');
                }
                
                // HOD cannot modify other HODs or system_admin users
                if ($targetUser && in_array($targetUser->user_type, ['hod', 'system_admin'])) {
                    $validator->errors()->add('user', 'You do not have permission to modify this user.');
                }
            }
            
            // Validate group assignment restrictions for HODs
            if ($user->user_type === 'hod' && $this->has('group_id')) {
                $groupId = $this->input('group_id');
                
                // HOD can only assign users to their own group
                if ($groupId != $user->group_id) {
                    $validator->errors()->add('group_id', 'You can only assign users to your own group.');
                }
            }
            
            // Ensure target user belongs to same company
            if ($targetUser && $targetUser->company_id !== $user->company_id) {
                $validator->errors()->add('user', 'User not found or access denied.');
            }
        });
    }
}