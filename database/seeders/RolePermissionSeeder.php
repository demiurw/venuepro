<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // VenuePro Internal Admin permissions
            'system_config',
            'user_management',
            'billing_access',
            'support_access',
            'global_booking_access',

            // System Admin permissions
            'manage_company_settings',
            'manage_buildings',
            'manage_rooms',
            'manage_company_users',
            'internal_billing_management',
            'book_on_behalf_of_company_users',
            'transfer_all_company_bookings',

            // Head of Department permissions
            'manage_groups',
            'manage_group_members',
            'create_booking_agents',
            'view_group_bookings',
            'book_on_behalf_of_group_members',
            'transfer_group_bookings',

            // Booking Agent permissions
            'create_bookings',
            'modify_bookings',
            'invite_attendees',
            'manage_group_bookings',

            // Invitee permissions
            'accept_decline_invites',
            'view_meeting_details',

            // External User permissions
            'create_public_bookings',
            'invite_to_own_bookings',
            'view_public_rooms',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles with their permissions and auth methods
        $roles = [
            'VenuePro Internal Admin' => [
                'permissions' => [
                    'system_config',
                    'user_management',
                    'billing_access',
                    'support_access',
                    'global_booking_access',
                ],
                'auth_methods' => ['otp'],
                'description' => 'Internal VenuePro administrators with system-wide access and configuration privileges.'
            ],
            'System Admin' => [
                'permissions' => [
                    'manage_company_settings',
                    'manage_buildings',
                    'manage_rooms',
                    'manage_company_users',
                    'internal_billing_management',
                    'book_on_behalf_of_company_users',
                    'transfer_all_company_bookings',
                ],
                'auth_methods' => ['otp', 'oauth'],
                'description' => 'Company system administrators with full control over company resources and users.'
            ],
            'Head of Department' => [
                'permissions' => [
                    'manage_groups',
                    'manage_group_members',
                    'create_booking_agents',
                    'view_group_bookings',
                    'book_on_behalf_of_group_members',
                    'transfer_group_bookings',
                ],
                'auth_methods' => ['otp', 'oauth'],
                'description' => 'Department heads with authority to manage groups and their members\' bookings.'
            ],
            'Booking Agent' => [
                'permissions' => [
                    'create_bookings',
                    'modify_bookings',
                    'invite_attendees',
                    'view_group_bookings',
                    'manage_group_bookings',
                ],
                'auth_methods' => ['otp', 'oauth'],
                'description' => 'Booking agents responsible for creating and managing bookings for their groups.'
            ],
            'Invitee' => [
                'permissions' => [
                    'accept_decline_invites',
                    'view_meeting_details',
                ],
                'auth_methods' => ['otp', 'oauth'],
                'description' => 'Invited users who can respond to invitations and view meeting details.'
            ],
            'External User' => [
                'permissions' => [
                    'create_public_bookings',
                    'invite_to_own_bookings',
                    'view_public_rooms',
                ],
                'auth_methods' => ['otp', 'oauth'],
                'description' => 'External users with limited access to public booking features.'
            ]
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ], [
                'description' => $roleData['description'],
                'allowed_auth_methods' => json_encode($roleData['auth_methods'])
            ]);

            // Update existing role if it exists
            if ($role->wasRecentlyCreated === false) {
                $role->update([
                    'description' => $roleData['description'],
                    'allowed_auth_methods' => json_encode($roleData['auth_methods'])
                ]);
            }

            // Assign permissions to role
            $role->syncPermissions($roleData['permissions']);
        }

        $this->command->info('Roles and permissions have been seeded successfully!');
        $this->command->info('Created ' . count($permissions) . ' permissions and ' . count($roles) . ' roles.');
    }
}