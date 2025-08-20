<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Room;
use App\Models\Group;
use App\Models\User;
use App\Models\AccessControl;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OnboardingService
{
    /**
     * Check if the company has completed onboarding
     */
    public function isOnboardingComplete(int $companyId): bool
    {
        $systemAdmin = User::where('company_id', $companyId)
            ->where('user_type', 'system_admin')
            ->first();

        return $systemAdmin && $systemAdmin->onboarding_step_completed >= 5;
    }

    /**
     * Get onboarding progress status
     */
    public function getOnboardingProgress(int $companyId): array
    {
        $systemAdmin = User::where('company_id', $companyId)
            ->where('user_type', 'system_admin')
            ->first();

        $stepCompleted = $systemAdmin ? $systemAdmin->onboarding_step_completed : 0;

        return [
            'buildings' => $stepCompleted >= 1,
            'rooms' => $stepCompleted >= 2,
            'groups' => $stepCompleted >= 3,
            'users' => $stepCompleted >= 4,
            'labels' => $stepCompleted >= 5,
            'current_step' => $stepCompleted,
            'next_step' => $stepCompleted < 5 ? $stepCompleted + 1 : null,
        ];
    }

    /**
     * Create multiple buildings for the company
     */
    public function createBuildings(User $user, array $buildingsData): array
    {
        try {
            DB::beginTransaction();

            $createdBuildings = [];
            foreach ($buildingsData as $buildingData) {
                $building = Building::create([
                    'name' => $buildingData['name'],
                    'description' => $buildingData['description'] ?? null,
                    'address_line1' => $buildingData['address_line1'],
                    'address_line2' => $buildingData['address_line2'] ?? null,
                    'city' => $buildingData['city'],
                    'state_id' => $buildingData['state_id'] ?? null,
                    'country_id' => $buildingData['country_id'],
                    'postal_code' => $buildingData['postal_code'] ?? null,
                    'timezone' => $buildingData['timezone'] ?? 'UTC',
                    'company_id' => $user->company_id,
                ]);
                $createdBuildings[] = $building;
            }

            DB::commit();

            // Update user's onboarding step to step 1 completed
            $user->onboarding_step_completed = 1;
            $user->save();

            Log::info('Buildings created during onboarding', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'buildings_count' => count($createdBuildings),
            ]);

            return [
                'success' => true,
                'message' => count($createdBuildings) . ' building(s) created successfully.',
                'data' => $createdBuildings,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create buildings during onboarding', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create buildings. Please try again.',
            ];
        }
    }

    /**
     * Create multiple rooms for the company with automatic group permissions
     */
    public function createRooms(User $user, array $roomsData): array
    {
        try {
            DB::beginTransaction();

            $createdRooms = [];
            foreach ($roomsData as $roomData) {
                $room = Room::create([
                    'name' => $roomData['name'],
                    'building_id' => $roomData['building_id'],
                    'capacity' => $roomData['capacity'],
                    'company_id' => $user->company_id,
                ]);
                $createdRooms[] = $room;
            }

            // Automatically assign booking permissions to all active groups
            $this->assignRoomPermissionsToGroups($user->company_id, $createdRooms);

            // Update user's onboarding step to step 2 completed
            $user->onboarding_step_completed = 2;
            $user->save();

            DB::commit();

            Log::info('Rooms created during onboarding', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'rooms_count' => count($createdRooms),
            ]);

            return [
                'success' => true,
                'message' => count($createdRooms) . ' room(s) created successfully with automatic group permissions.',
                'data' => $createdRooms,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create rooms during onboarding', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create rooms. Please try again.',
            ];
        }
    }

    /**
     * Create multiple groups for the company
     */
    public function createGroups(User $user, array $groupsData): array
    {
        try {
            DB::beginTransaction();

            $createdGroups = [];
            foreach ($groupsData as $groupData) {
                $group = Group::create([
                    'name' => $groupData['name'],
                    'description' => $groupData['description'],
                    'company_id' => $user->company_id,
                    'created_by' => $user->id,
                    'is_active' => true,
                ]);
                $createdGroups[] = $group;
            }

            // Automatically assign permissions for existing rooms
            $this->assignExistingRoomsToGroups($user->company_id, $createdGroups);

            // Update user's onboarding step to step 3 completed
            $user->onboarding_step_completed = 3;
            $user->save();

            DB::commit();

            Log::info('Groups created during onboarding', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'groups_count' => count($createdGroups),
            ]);

            return [
                'success' => true,
                'message' => count($createdGroups) . ' group(s) created successfully with room permissions.',
                'data' => $createdGroups,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create groups during onboarding', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create groups. Please try again.',
            ];
        }
    }

    /**
     * Create multiple users for the company
     */
    public function createUsers(User $currentUser, array $usersData): array
    {
        // Map user types to role names
        $userTypeToRoleMapping = [
            'hod' => 'Head of Department',
            'booking_agent' => 'Booking Agent',
            'invitee' => 'Invitee',
        ];

        try {
            DB::beginTransaction();

            $createdUsers = [];
            foreach ($usersData as $userData) {
                $roleName = $userTypeToRoleMapping[$userData['user_type']];
                $role = Role::where('name', $roleName)->first();
                
                if (!$role) {
                    throw new Exception("Role '{$roleName}' not found for user type '{$userData['user_type']}'");
                }

                $newUser = User::create([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'user_type' => $userData['user_type'],
                    'role_id' => $role->id,
                    'group_id' => $userData['group_id'] ?? null,
                    'company_id' => $currentUser->company_id,
                    'status' => UserStatus::PENDING,
                    'auth_method' => 'otp',
                ]);
                $createdUsers[] = $newUser;
            }

            // Update user's onboarding step to step 4 completed
            $currentUser->onboarding_step_completed = 4;
            $currentUser->save();

            DB::commit();

            Log::info('Users created during onboarding', [
                'created_by' => $currentUser->id,
                'company_id' => $currentUser->company_id,
                'users_count' => count($createdUsers),
            ]);

            return [
                'success' => true,
                'message' => count($createdUsers) . ' user(s) created successfully.',
                'data' => $createdUsers,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create users during onboarding', [
                'error' => $e->getMessage(),
                'created_by' => $currentUser->id,
                'company_id' => $currentUser->company_id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create users. Please try again.',
            ];
        }
    }

    /**
     * Save company labels as the final step
     */
    public function saveCompanyLabels(User $user, array $labelsData): array
    {
        try {
            DB::beginTransaction();

            $company = Company::find($user->company_id);
            if (!$company) {
                throw new Exception('Company not found');
            }

            // Store labels in the custom_labels JSON column
            $company->custom_labels = $labelsData;
            $company->save();

            // Update user's onboarding step to completed (step 5)
            $user->onboarding_step_completed = 5;
            $user->save();

            DB::commit();

            Log::info('Company labels saved during onboarding', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'labels_count' => count($labelsData),
            ]);

            return [
                'success' => true,
                'message' => 'Company labels saved successfully. Onboarding completed!',
                'data' => $labelsData,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to save company labels during onboarding', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to save company labels. Please try again.',
            ];
        }
    }

    /**
     * Get available buildings for room creation
     */
    public function getAvailableBuildings(int $companyId): array
    {
        return Building::where('company_id', $companyId)
            ->with(['state', 'country'])
            ->get(['id', 'name', 'address_line1', 'address_line2', 'city', 'state_id', 'country_id', 'postal_code'])
            ->map(function ($building) {
                // Create a formatted address string
                $addressParts = array_filter([
                    $building->address_line1,
                    $building->address_line2,
                    $building->city,
                    $building->state ? $building->state->name : null,
                    $building->postal_code,
                    $building->country ? $building->country->name : null,
                ]);
                
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'address' => implode(', ', $addressParts),
                ];
            })
            ->toArray();
    }

    /**
     * Get available groups for user assignment
     */
    public function getAvailableGroups(int $companyId): array
    {
        return Group::where('company_id', $companyId)
            ->where('is_active', true)
            ->select('id', 'name', 'description')
            ->get()
            ->toArray();
    }

    /**
     * Automatically assign room permissions to groups
     */
    private function assignRoomPermissionsToGroups(int $companyId, array $rooms): void
    {
        $groups = Group::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ($rooms as $room) {
            foreach ($groups as $group) {
                AccessControl::create([
                    'group_id' => $group->id,
                    'entity_id' => $room->id,
                    'entity_type' => 'room',
                    'access_level' => 'book',
                    'company_id' => $companyId,
                ]);
            }
        }
    }

    /**
     * Assign existing rooms to newly created groups
     */
    private function assignExistingRoomsToGroups(int $companyId, array $groups): void
    {
        $rooms = Room::where('company_id', $companyId)->get();

        foreach ($groups as $group) {
            foreach ($rooms as $room) {
                AccessControl::create([
                    'group_id' => $group->id,
                    'entity_id' => $room->id,
                    'entity_type' => 'room',
                    'access_level' => 'book',
                    'company_id' => $companyId,
                ]);
            }
        }
    }
}