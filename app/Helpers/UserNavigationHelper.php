<?php

namespace App\Helpers;

use App\Models\User;

class UserNavigationHelper
{
    /**
     * Navigation menus for different user types
     */
    private const NAVIGATION_MENUS = [
        'venuepro_admin' => [
            ['name' => 'Dashboard', 'route' => 'venuepro-admin.dashboard', 'icon' => 'dashboard'],
            ['name' => 'Companies', 'route' => 'venuepro-admin.companies', 'icon' => 'business'],
            ['name' => 'System Settings', 'route' => 'venuepro-admin.system-settings', 'icon' => 'settings'],
            ['name' => 'Analytics', 'route' => 'venuepro-admin.analytics', 'icon' => 'analytics'],
        ],
        'system_admin' => [
            ['name' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
            ['name' => 'Buildings', 'route' => 'admin.buildings', 'icon' => 'building'],
            ['name' => 'Rooms', 'route' => 'admin.rooms', 'icon' => 'room'],
            ['name' => 'User Management', 'route' => 'admin.users.index', 'icon' => 'users'],
            ['name' => 'Groups', 'route' => 'admin.groups.index', 'icon' => 'group'],
            ['name' => 'Reports', 'route' => 'admin.reports', 'icon' => 'assessment'],
            ['name' => 'Settings', 'route' => 'admin.settings', 'icon' => 'settings'],
        ],
        'hod' => [
            ['name' => 'Dashboard', 'route' => 'hod.dashboard', 'icon' => 'dashboard'],
            ['name' => 'My Team', 'route' => 'hod.team', 'icon' => 'people'],
            ['name' => 'Bookings', 'route' => 'hod.bookings', 'icon' => 'event'],
            ['name' => 'Approvals', 'route' => 'hod.approvals', 'icon' => 'approval'],
            ['name' => 'Reports', 'route' => 'hod.reports', 'icon' => 'assessment'],
        ],
        'booking_agent' => [
            ['name' => 'Dashboard', 'route' => 'agent.dashboard', 'icon' => 'dashboard'],
            ['name' => 'Bookings', 'route' => 'agent.bookings', 'icon' => 'event'],
            ['name' => 'Calendar', 'route' => 'agent.calendar', 'icon' => 'calendar'],
            ['name' => 'Clients', 'route' => 'agent.clients', 'icon' => 'people'],
        ],
        'invitee' => [
            ['name' => 'Dashboard', 'route' => 'user.dashboard', 'icon' => 'dashboard'],
            ['name' => 'My Bookings', 'route' => 'user.bookings', 'icon' => 'event'],
            ['name' => 'Calendar', 'route' => 'user.calendar', 'icon' => 'calendar'],
            ['name' => 'Profile', 'route' => 'user.profile', 'icon' => 'person'],
        ],
        'external' => [
            ['name' => 'Dashboard', 'route' => 'external.dashboard', 'icon' => 'dashboard'],
            ['name' => 'My Bookings', 'route' => 'external.bookings', 'icon' => 'event'],
            ['name' => 'Requests', 'route' => 'external.requests', 'icon' => 'request'],
        ],
    ];

    /**
     * User type display names
     */
    private const USER_TYPE_NAMES = [
        'venuepro_admin' => 'VenuePro Administrator',
        'system_admin' => 'System Administrator',
        'hod' => 'Head of Department',
        'booking_agent' => 'Booking Agent',
        'invitee' => 'User',
        'external' => 'External User',
    ];

    /**
     * Get navigation menu for a user
     */
    public static function getNavigationMenu(User $user): array
    {
        if ($user->status !== 'active') {
            return [];
        }

        return self::NAVIGATION_MENUS[$user->user_type] ?? [];
    }

    /**
     * Get user type display name
     */
    public static function getUserTypeDisplayName(User $user): string
    {
        return self::USER_TYPE_NAMES[$user->user_type] ?? 'Unknown';
    }

    /**
     * Get dashboard route for user type
     */
    public static function getDashboardRoute(string $userType): string
    {
        $routes = [
            'venuepro_admin' => 'venuepro-admin.dashboard',
            'system_admin' => 'admin.dashboard',
            'hod' => 'hod.dashboard',
            'booking_agent' => 'agent.dashboard',
            'invitee' => 'user.dashboard',
            'external' => 'external.dashboard',
        ];

        return $routes[$userType] ?? 'user.dashboard';
    }

    /**
     * Check if user can access a specific feature
     */
    public static function canAccessFeature(User $user, string $feature): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        $permissions = [
            'venuepro_admin' => [
                'manage_companies', 'system_settings', 'global_analytics',
                'manage_all_users', 'view_all_bookings', 'system_configuration'
            ],
            'system_admin' => [
                'manage_buildings', 'manage_rooms', 'manage_users',
                'manage_groups', 'view_reports', 'company_settings',
                'approve_bookings', 'manage_amenities'
            ],
            'hod' => [
                'view_team', 'manage_team_bookings', 'approve_team_requests',
                'view_team_reports', 'manage_department_budget'
            ],
            'booking_agent' => [
                'create_bookings', 'manage_bookings', 'view_calendar',
                'manage_clients', 'handle_external_requests'
            ],
            'invitee' => [
                'create_bookings', 'view_own_bookings', 'view_calendar',
                'manage_profile', 'request_rooms'
            ],
            'external' => [
                'view_own_bookings', 'request_bookings', 'view_requests'
            ],
        ];

        $userPermissions = $permissions[$user->user_type] ?? [];
        return in_array($feature, $userPermissions);
    }

    /**
     * Get breadcrumb trail for current route
     */
    public static function getBreadcrumbs(User $user, string $currentRoute): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => self::getDashboardRoute($user->user_type)]
        ];

        $routeMappings = [
            // Admin routes
            'admin.buildings' => ['name' => 'Buildings', 'parent' => 'admin.dashboard'],
            'admin.rooms' => ['name' => 'Rooms', 'parent' => 'admin.dashboard'],
            'admin.users.index' => ['name' => 'Users', 'parent' => 'admin.dashboard'],
            'admin.groups.index' => ['name' => 'Groups', 'parent' => 'admin.dashboard'],
            'admin.reports' => ['name' => 'Reports', 'parent' => 'admin.dashboard'],
            'admin.settings' => ['name' => 'Settings', 'parent' => 'admin.dashboard'],

            // HOD routes
            'hod.team' => ['name' => 'My Team', 'parent' => 'hod.dashboard'],
            'hod.bookings' => ['name' => 'Bookings', 'parent' => 'hod.dashboard'],
            'hod.approvals' => ['name' => 'Approvals', 'parent' => 'hod.dashboard'],
            'hod.reports' => ['name' => 'Reports', 'parent' => 'hod.dashboard'],

            // Agent routes
            'agent.bookings' => ['name' => 'Bookings', 'parent' => 'agent.dashboard'],
            'agent.calendar' => ['name' => 'Calendar', 'parent' => 'agent.dashboard'],
            'agent.clients' => ['name' => 'Clients', 'parent' => 'agent.dashboard'],

            // User routes
            'user.bookings' => ['name' => 'My Bookings', 'parent' => 'user.dashboard'],
            'user.calendar' => ['name' => 'Calendar', 'parent' => 'user.dashboard'],
            'user.profile' => ['name' => 'Profile', 'parent' => 'user.dashboard'],

            // External routes
            'external.bookings' => ['name' => 'My Bookings', 'parent' => 'external.dashboard'],
            'external.requests' => ['name' => 'Requests', 'parent' => 'external.dashboard'],

            // VenuePro Admin routes
            'venuepro-admin.companies' => ['name' => 'Companies', 'parent' => 'venuepro-admin.dashboard'],
            'venuepro-admin.system-settings' => ['name' => 'System Settings', 'parent' => 'venuepro-admin.dashboard'],
            'venuepro-admin.analytics' => ['name' => 'Analytics', 'parent' => 'venuepro-admin.dashboard'],
        ];

        if (isset($routeMappings[$currentRoute])) {
            $breadcrumbs[] = [
                'name' => $routeMappings[$currentRoute]['name'],
                'route' => $currentRoute,
                'active' => true
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Get quick actions available to user
     */
    public static function getQuickActions(User $user): array
    {
        if ($user->status !== 'active') {
            return [];
        }

        $actions = [
            'venuepro_admin' => [
                ['name' => 'Add Company', 'route' => 'venuepro-admin.companies.create', 'icon' => 'add_business'],
                ['name' => 'System Health', 'route' => 'venuepro-admin.system.health', 'icon' => 'health'],
                ['name' => 'Global Reports', 'route' => 'venuepro-admin.reports', 'icon' => 'assessment'],
            ],
            'system_admin' => [
                ['name' => 'Add User', 'route' => 'admin.users.create', 'icon' => 'person_add'],
                ['name' => 'Add Room', 'route' => 'admin.rooms.create', 'icon' => 'add_location'],
                ['name' => 'View Reports', 'route' => 'admin.reports', 'icon' => 'assessment'],
            ],
            'hod' => [
                ['name' => 'Team Overview', 'route' => 'hod.team', 'icon' => 'people'],
                ['name' => 'Pending Approvals', 'route' => 'hod.approvals', 'icon' => 'approval'],
                ['name' => 'Team Reports', 'route' => 'hod.reports', 'icon' => 'assessment'],
            ],
            'booking_agent' => [
                ['name' => 'New Booking', 'route' => 'agent.bookings.create', 'icon' => 'event_note'],
                ['name' => 'Calendar View', 'route' => 'agent.calendar', 'icon' => 'calendar'],
                ['name' => 'Add Client', 'route' => 'agent.clients.create', 'icon' => 'person_add'],
            ],
            'invitee' => [
                ['name' => 'Book Room', 'route' => 'user.bookings.create', 'icon' => 'event_note'],
                ['name' => 'View Calendar', 'route' => 'user.calendar', 'icon' => 'calendar'],
                ['name' => 'My Profile', 'route' => 'user.profile', 'icon' => 'person'],
            ],
            'external' => [
                ['name' => 'Request Booking', 'route' => 'external.requests.create', 'icon' => 'event_note'],
                ['name' => 'View Requests', 'route' => 'external.requests', 'icon' => 'list'],
            ],
        ];

        return $actions[$user->user_type] ?? [];
    }

    /**
     * Get user permissions summary
     */
    public static function getUserPermissionsSummary(User $user): array
    {
        $quickActions = self::getQuickActions($user);
        
        $summary = [
            'user_type' => $user->user_type,
            'display_name' => self::getUserTypeDisplayName($user),
            'navigation' => self::getNavigationMenu($user),
            'dashboard_route' => self::getDashboardRoute($user->user_type),
            'quick_actions' => is_array($quickActions) ? $quickActions : [],
            'can_manage_users' => self::canAccessFeature($user, 'manage_users'),
            'can_manage_bookings' => self::canAccessFeature($user, 'manage_bookings'),
            'can_view_reports' => self::canAccessFeature($user, 'view_reports'),
            'can_approve_requests' => self::canAccessFeature($user, 'approve_bookings'),
        ];

        return $summary;
    }
}
