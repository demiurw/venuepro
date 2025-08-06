<?php

namespace App\Services;

use App\Models\User;
use App\Models\Group;
use App\Enums\UserStatus;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Auth\OtpService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $userRepository;
    protected $otpService;

    /**
     * Role hierarchy for permission checks
     */
    protected $roleHierarchy = [
        'system_admin' => ['hod', 'booking_agent', 'invitee'],
        'hod' => ['booking_agent', 'invitee'],
        'booking_agent' => [],
        'invitee' => []
    ];

    /**
     * Mapping of user types to role IDs
     */
    protected $userTypeToRoleMapping = [
        'hod' => 1,
        'booking_agent' => 4,
        'invitee' => 3,
    ];

    public function __construct(UserRepositoryInterface $userRepository, OtpService $otpService)
    {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
    }

    /**
     * Get allowed roles for the current user.
     *
     * @param User $currentUser
     * @return array
     */
    public function getAllowedRoles(User $currentUser): array
    {
        return $this->roleHierarchy[$currentUser->user_type] ?? [];
    }

    /**
     * Get paginated company users with proper role filtering.
     *
     * @param User $currentUser
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(User $currentUser, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $allowedRoles = $this->getAllowedRoles($currentUser);
        
        // If HOD, additionally filter by group
        if ($currentUser->user_type === 'hod' && $currentUser->group_id) {
            $filters['group_id'] = $currentUser->group_id;
        }

        return $this->userRepository->getPaginatedCompanyUsers($filters, $allowedRoles, $perPage);
    }

    /**
     * Get user statistics for the current user's scope.
     *
     * @param User $currentUser
     * @return array
     */
    public function getUserStatistics(User $currentUser): array
    {
        $allowedRoles = $this->getAllowedRoles($currentUser);
        return $this->userRepository->getRoleStatistics($allowedRoles);
    }

    /**
     * Get user booking statistics.
     *
     * @param int $userId
     * @return array
     */
    public function getUserBookingStatistics(int $userId): array
    {
        return $this->userRepository->getUserBookingStatistics($userId);
    }

    /**
     * Find a user that can be managed by the current user.
     *
     * @param User $currentUser
     * @param int $userId
     * @return User|null
     */
    public function findManageableUser(User $currentUser, int $userId): ?User
    {
        $allowedRoles = $this->getAllowedRoles($currentUser);
        $user = $this->userRepository->findCompanyUser($userId, $allowedRoles);

        // Additional check for HOD permissions
        if ($user && $currentUser->user_type === 'hod') {
            if ($user->group_id !== $currentUser->group_id) {
                return null; // HOD can only manage users in their group
            }
        }

        return $user;
    }

    /**
     * Create a new company user with proper validation and OTP setup.
     *
     * @param User $currentUser
     * @param array $validatedData
     * @return array
     */
    public function createUser(User $currentUser, array $validatedData): array
    {
        try {
            DB::beginTransaction();

            // Get the role ID for the user type
            $roleId = $this->userTypeToRoleMapping[$validatedData['user_type']];

            // Prepare user data
            $userData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'user_type' => $validatedData['user_type'],
                'role_id' => $roleId,
                'group_id' => $validatedData['group_id'] ?? null,
                'company_id' => $currentUser->company_id,
                'status' => UserStatus::PENDING,
                'auth_method' => 'otp',
            ];

            // Create the user
            $newUser = $this->userRepository->createCompanyUser($userData);

            // Generate initial OTP for account setup
            $otpResult = $this->otpService->generateOtpForEmail(
                $validatedData['email'], 
                'account_setup'
            );

            if (!$otpResult['success']) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to send setup email to the user. Please try again.',
                    'user' => null
                ];
            }

            DB::commit();

            Log::info('New user created', [
                'created_user_id' => $newUser->id,
                'created_by' => $currentUser->id,
                'company_id' => $currentUser->company_id,
                'user_type' => $validatedData['user_type']
            ]);

            return [
                'success' => true,
                'message' => "User {$newUser->full_name} has been created successfully. Setup instructions have been sent to their email.",
                'user' => $newUser
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create new user', [
                'error' => $e->getMessage(),
                'created_by' => $currentUser->id,
                'company_id' => $currentUser->company_id,
                'data' => $validatedData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create user. Please try again.',
                'user' => null
            ];
        }
    }

    /**
     * Update a user with proper validation and permission checks.
     *
     * @param User $currentUser
     * @param int $userId
     * @param array $validatedData
     * @return array
     */
    public function updateUser(User $currentUser, int $userId, array $validatedData): array
    {
        try {
            $allowedRoles = $this->getAllowedRoles($currentUser);
            $targetUser = $this->userRepository->findCompanyUserOrFail($userId, $allowedRoles);

            // Additional permission checks for HOD
            if ($currentUser->user_type === 'hod') {
                if ($targetUser->group_id !== $currentUser->group_id) {
                    return [
                        'success' => false,
                        'message' => 'You do not have permission to update this user.',
                        'user' => null
                    ];
                }
            }

            $emailChanged = $targetUser->email !== $validatedData['email'];
            
            // Get the role ID for the user type
            $roleId = $this->userTypeToRoleMapping[$validatedData['user_type']];

            // Prepare update data
            $updateData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'user_type' => $validatedData['user_type'],
                'role_id' => $roleId,
                'group_id' => $validatedData['group_id'] ?? null,
            ];

            // Only system_admin can update status
            if ($currentUser->user_type === 'system_admin' && isset($validatedData['status'])) {
                $updateData['status'] = $validatedData['status'];
            }

            // Update the user
            $updatedUser = $this->userRepository->updateCompanyUser($userId, $updateData, $allowedRoles);

            // Handle email change notification
            if ($emailChanged && $updatedUser->isActive()) {
                $otpResult = $this->otpService->generateOtp($updatedUser, 'email_change_verification');
                
                if ($otpResult['success']) {
                    Log::info('Email change verification OTP sent', [
                        'user_id' => $updatedUser->id,
                        'old_email' => $targetUser->email,
                        'new_email' => $validatedData['email'],
                        'updated_by' => $currentUser->id
                    ]);
                }
            }

            Log::info('User updated', [
                'updated_user_id' => $updatedUser->id,
                'updated_by' => $currentUser->id,
                'email_changed' => $emailChanged
            ]);

            $message = "User {$updatedUser->full_name} has been updated successfully.";
            if ($emailChanged) {
                $message .= " A verification email has been sent to the new email address.";
            }

            return [
                'success' => true,
                'message' => $message,
                'user' => $updatedUser,
                'email_changed' => $emailChanged
            ];

        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'updated_by' => $currentUser->id,
                'data' => $validatedData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update user. Please try again.',
                'user' => null
            ];
        }
    }

    /**
     * Deactivate a user with proper checks.
     *
     * @param User $currentUser
     * @param int $userId
     * @return array
     */
    public function deactivateUser(User $currentUser, int $userId): array
    {
        try {
            DB::beginTransaction();

            $allowedRoles = $this->getAllowedRoles($currentUser);
            $targetUser = $this->userRepository->findCompanyUserOrFail($userId, $allowedRoles);

            // Additional permission checks for HOD
            if ($currentUser->user_type === 'hod') {
                if ($targetUser->group_id !== $currentUser->group_id) {
                    return [
                        'success' => false,
                        'message' => 'You do not have permission to deactivate this user.',
                    ];
                }
            }

            // Prevent self-deactivation
            if ($targetUser->id === $currentUser->id) {
                return [
                    'success' => false,
                    'message' => 'You cannot deactivate your own account.',
                ];
            }

            // Check if user is already inactive
            if ($targetUser->isInactive()) {
                return [
                    'success' => false,
                    'message' => 'User is already inactive.',
                ];
            }

            // Check for active bookings
            $activeBookings = $targetUser->allBookings()
                ->where(function($query) {
                    $query->where('date', '>', now()->toDateString())
                          ->orWhere(function($q) {
                              $q->where('date', '=', now()->toDateString())
                                ->whereTime('start_time', '>=', now()->toTimeString());
                          });
                })
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($activeBookings > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot deactivate user {$targetUser->full_name}. They have {$activeBookings} active booking(s). Please cancel or reassign these bookings first.",
                ];
            }

            $userName = $targetUser->full_name;
            $this->userRepository->deactivateCompanyUser($userId, $allowedRoles);

            DB::commit();

            Log::info('User deactivated', [
                'deactivated_user_id' => $userId,
                'deactivated_user_name' => $userName,
                'deactivated_by' => $currentUser->id,
                'company_id' => $currentUser->company_id
            ]);

            return [
                'success' => true,
                'message' => "User {$userName} has been deactivated successfully.",
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to deactivate user', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'deactivated_by' => $currentUser->id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to deactivate user. Please try again.',
            ];
        }
    }

    /**
     * Reactivate a user.
     *
     * @param User $currentUser
     * @param int $userId
     * @return array
     */
    public function reactivateUser(User $currentUser, int $userId): array
    {
        try {
            $allowedRoles = $this->getAllowedRoles($currentUser);
            $targetUser = $this->userRepository->findCompanyUserOrFail($userId, $allowedRoles);

            // Additional permission checks for HOD
            if ($currentUser->user_type === 'hod') {
                if ($targetUser->group_id !== $currentUser->group_id) {
                    return [
                        'success' => false,
                        'message' => 'You do not have permission to reactivate this user.',
                    ];
                }
            }

            // Check if user is already active
            if ($targetUser->isActive()) {
                return [
                    'success' => false,
                    'message' => 'User is already active.',
                ];
            }

            $userName = $targetUser->full_name;
            $this->userRepository->reactivateCompanyUser($userId, $allowedRoles);

            Log::info('User reactivated', [
                'reactivated_user_id' => $userId,
                'reactivated_user_name' => $userName,
                'reactivated_by' => $currentUser->id,
                'company_id' => $currentUser->company_id
            ]);

            return [
                'success' => true,
                'message' => "User {$userName} has been reactivated successfully.",
            ];

        } catch (\Exception $e) {
            Log::error('Failed to reactivate user', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'reactivated_by' => $currentUser->id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reactivate user. Please try again.',
            ];
        }
    }

    /**
     * Get active groups for user assignment based on current user permissions.
     *
     * @param User $currentUser
     * @return Collection
     */
    public function getAvailableGroups(User $currentUser): Collection
    {
        $query = Group::where('company_id', $currentUser->company_id)
            ->where('is_active', true);

        // HOD can only see their own group
        if ($currentUser->user_type === 'hod' && $currentUser->group_id) {
            $query->where('id', $currentUser->group_id);
        }

        return $query->orderBy('name')->get(['id', 'name', 'description']);
    }

    /**
     * Get role requirements configuration.
     *
     * @return array
     */
    public function getRoleRequirements(): array
    {
        return [
            'booking_agent' => ['group_required' => true],
            'hod' => ['group_required' => true],
            'invitee' => ['group_required' => false],
        ];
    }

    /**
     * Resend OTP for a user.
     *
     * @param User $currentUser
     * @param int $userId
     * @return array
     */
    public function resendOtp(User $currentUser, int $userId): array
    {
        try {
            $allowedRoles = $this->getAllowedRoles($currentUser);
            $targetUser = $this->userRepository->findCompanyUserOrFail($userId, $allowedRoles);

            // Additional permission checks for HOD
            if ($currentUser->user_type === 'hod') {
                if ($targetUser->group_id !== $currentUser->group_id) {
                    return [
                        'success' => false,
                        'message' => 'You do not have permission to manage this user.',
                    ];
                }
            }

            // Determine OTP purpose based on user status
            $purpose = $targetUser->isPending() ? 'account_setup' : 'login';

            $otpResult = $this->otpService->generateOtp($targetUser, $purpose);

            if (!$otpResult['success']) {
                return [
                    'success' => false,
                    'message' => $otpResult['message'],
                ];
            }

            Log::info('OTP resent by user', [
                'user_id' => $targetUser->id,
                'requested_by' => $currentUser->id,
                'purpose' => $purpose
            ]);

            return [
                'success' => true,
                'message' => "Setup instructions have been resent to {$targetUser->full_name}'s email address.",
            ];

        } catch (\Exception $e) {
            Log::error('Failed to resend OTP', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'requested_by' => $currentUser->id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to resend setup instructions. Please try again.',
            ];
        }
    }
}