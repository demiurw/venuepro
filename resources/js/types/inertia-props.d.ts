// resources/js/types/app-types.d.ts

// --- Core Application-Specific Types ---

// User Interface: Must match the data structure of your authenticated user
// returned by your Laravel UserResource (or directly from $request->user()).
interface User {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string; // Computed property on User model/resource
    email: string;
    user_type: string; // e.g., 'system_admin', 'hod', 'booking_agent'
    user_type_display_name: string; // Computed property
    dashboard_route: string; // Dynamic route name
    navigation_menu: Array<{ route: string; name: string }>; // Array of navigation links
    permissions: {
        quick_actions: Array<{ route: string; name: string }>; // Array of quick action objects
        can_view_reports: boolean; // Boolean permission check
        // Add any other permissions expected by your frontend logic from user.permissions
    };
    company?: { // Optional, as some users (e.g., VenuePro Internal Admin) might not have a company directly.
        id: number; //
        name: string; //
        slug?: string; // Often useful for tenant identification in frontend
        // Add other company properties needed in frontend
    };
    // Add any other user attributes (e.g., phone, status, profile_photo_url)
}

// Auth Interface: Represents the structure of the 'auth' prop
interface Auth {
    user: User | null; // User can be null if not authenticated
}

// Flash Messages Interface: As used in your template
interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    // Add other flash message types if you have them
}

// Ziggy's Config Interface: Represents the structure of the 'ziggy' prop
// You might need to import this from 'ziggy-js' if it's strictly typed there,
// otherwise this basic definition is sufficient for now.
interface ZiggyConfig {
    url: string;
    port: number | null;
    routes: Record<string, { uri: string, methods: string[], bindings?: Record<string, string>, domain?: string }>;
    baseUrl: string;
    defaults: Record<string, any>;
}


// --- Your Provided AppPageProps Definition ---
// This defines the full expected shape of `page.props`
export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string; // e.g., config('app.name')
    quote: { message: string; author: string }; // e.g., a dynamic quote
    auth: Auth; // Authenticated user data
    ziggy: ZiggyConfig & { location: string }; // Ziggy's routing data
    sidebarOpen: boolean; // e.g., global UI state for sidebar
    flash: FlashMessages; // Flash messages
    // Add any other top-level props your Laravel backend sends to ALL Inertia pages
    // e.g., errors?: Record<string, string>; // Laravel validation errors
};

// --- Augment Inertia's global PageProps interface ---
declare module '@inertiajs/core' {
    // This augments the PageProps interface used by Inertia's usePage() hook.
    // It tells TypeScript that the global PageProps *is* your AppPageProps.
    interface PageProps extends AppPageProps {}
}
