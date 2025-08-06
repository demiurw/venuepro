// resources/js/types/index.d.ts

import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    badge?: string;
}

// Navigation menu item for role-based menus
export interface NavigationMenuItem {
    name: string;
    route: string;
    icon: string;
}

// Quick action item
export interface QuickAction {
    name: string;
    route: string;
    icon: string;
}

// User permissions summary
export interface UserPermissions {
    user_type: string;
    display_name: string;
    navigation: NavigationMenuItem[];
    dashboard_route: string;
    quick_actions: QuickAction[];
    can_manage_users: boolean;
    can_manage_bookings: boolean;
    can_view_reports: boolean;
    can_approve_requests: boolean;
}

// Company information
export interface Company {
    id: number;
    name: string;
    slug: string;
    status: string;
    created_at: string;
    updated_at: string;
}

// Group information
export interface Group {
    id: number;
    name: string;
    description?: string;
    company_id: number;
    status: 'active' | 'inactive';
    member_count: number;
    active_members?: number;
    total_bookings?: number;
    created_at: string;
    updated_at: string;
    member_breakdown?: {
        admins: number;
        managers: number;
        members: number;
    };
}

// Group member information - no longer has group-specific roles
export interface GroupMember {
    user_id: number;
    group_id: number;
    added_at: string;
    added_by_id: number;
    user: User;
    added_by?: User;
}

// Role information
export interface Role {
    id: number;
    name: string;
    guard_name: string;
    team_id?: number;
    created_at: string;
    updated_at: string;
}

// Updated User interface with all required properties
export interface User {
    id: number;
    email: string;
    first_name: string;
    last_name: string;
    name: string; // Keep existing for compatibility
    full_name: string; // New computed property
    role_id: number;
    group_id?: number;
    company_id?: number;
    status: 'active' | 'inactive' | 'pending';
    user_type: 'venuepro_admin' | 'system_admin' | 'hod' | 'booking_agent' | 'invitee' | 'external';
    user_type_display_name: string; // New computed property
    auth_method: 'otp' | 'oauth';
    avatar?: string;
    email_verified_at: string | null;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;

    // Relationships
    company?: Company;
    group?: Group;
    role?: Role;

    // Role-based computed properties
    navigation_menu: NavigationMenuItem[];
    dashboard_route: string;
    permissions: UserPermissions;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;
