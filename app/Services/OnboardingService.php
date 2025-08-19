<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Room;
use App\Models\Group;
use App\Models\User;
use App\Models\AccessControl;
use App\Models\Company;
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
        $hasBuilding = Building::where('company_id', $companyId)->exists();
        $hasRoom = Room::where('company_id', $companyId)->exists();
        $hasGroup = Group::where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();
        $hasOtherUser = User::where('company_id', $companyId)
            ->whereIn('user_type', ['booking_agent', 'hod', 'invitee'])
            ->exists();

        return $hasBuilding && $hasRoom && $hasGroup && $hasOtherUser;
    }

    /**
     * Get onboarding progress status
     */
    public function getOnboardingProgress(int $companyId): array
    {
        return [
            'buildings' => Building::where('company_id', $companyId)->exists(),
            'rooms' => Room::where('company_id', $companyId)->exists(),
            'groups' => Group::where('company_id', $companyId)->where('is_active', true)->exists(),
            'users' => User::where('company_id', $companyId)
                ->whereIn('user_type', ['booking_agent', 'hod', 'invitee'])
                ->exists(),
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
                    'address' => $buildingData['address'],
                    'company_id' => $user->company_id,
                ]);
                $createdBuildings[] = $building;
            }

            DB::commit();

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
                    'type' => $roomData['type'],
                    'company_id' => $user->company_id,
                ]);
                $createdRooms[] = $room;
            }

            // Automatically assign booking permissions to all active groups
            $this->assignRoomPermissionsToGroups($user->company_id, $createdRooms);

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
        $userTypeToRoleMapping = [
            'hod' => 1,
            'booking_agent' => 4,
            'invitee' => 3,
        ];

        try {
            DB::beginTransaction();

            $createdUsers = [];
            foreach ($usersData as $userData) {
                $roleId = $userTypeToRoleMapping[$userData['user_type']];

                $newUser = User::create([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'user_type' => $userData['user_type'],
                    'role_id' => $roleId,
                    'group_id' => $userData['group_id'] ?? null,
                    'company_id' => $currentUser->company_id,
                    'status' => UserStatus::PENDING,
                    'auth_method' => 'otp',
                ]);
                $createdUsers[] = $newUser;
            }

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

            // Save labels to company_labels table or update company preferences
            // This depends on your database structure for company labels
            // For now, I'll assume we're updating the company record or storing in a separate table
            
            $company = Company::find($user->company_id);
            if (!$company) {
                throw new Exception('Company not found');
            }

            // Store labels in a JSON column or separate table
            // Assuming company has a labels column
            DB::table('company_labels')->where('company_id', $user->company_id)->delete();
            
            foreach ($labelsData as $label) {
                DB::table('company_labels')->insert([
                    'company_id' => $user->company_id,
                    'label_type' => $label['type'],
                    'label_value' => $label['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Company labels saved during onboarding', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'labels_count' => count($labelsData),
            ]);

            return [
                'success' => true,
                'message' => 'Company labels saved successfully.',
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
            ->select('id', 'name', 'address')
            ->get()
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
                    'resource_id' => $room->id,
                    'resource_type' => Room::class,
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
                    'resource_id' => $room->id,
                    'resource_type' => Room::class,
                    'company_id' => $companyId,
                ]);
            }
        }
    }
}