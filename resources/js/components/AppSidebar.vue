<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Calendar,
    MapPin,
    Users,
    Settings,
    BarChart3,
    BookOpenCheck,
    Shield,
    Bell,
    Building,
    Home,
    UserCheck,
    Users2,
    FileText,
    PlusSquare,
    ClipboardList
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Icon mapping for backend navigation items
const iconMap = {
    dashboard: LayoutGrid,
    building: Building,
    room: Home,
    users: Users,
    people: Users2,
    group: Users2,
    assessment: BarChart3,
    settings: Settings,
    event: Calendar,
    approval: UserCheck,
    person: Users,
    calendar: Calendar,
    request: ClipboardList,
    business: Building,
    analytics: BarChart3,
    health: Shield,
    person_add: PlusSquare,
    add_location: MapPin,
    event_note: BookOpenCheck,
    list: FileText
};

// Get navigation items from the backend user data
const mainNavItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;
    if (!user?.navigation_menu) return [];
    
    return user.navigation_menu.map((item: any) => ({
        title: item.name,
        href: route(item.route),
        icon: iconMap[item.icon] || LayoutGrid,
        badge: undefined,
    }));
});

// Get quick actions from the backend user data
const quickAccessItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;
    let items: NavItem[] = [];
    
    // Add User Management for system_admin users
    if (user?.user_type === 'system_admin') {
        items.push({
            title: 'User Management',
            href: route('admin.users.index'),
            icon: Users,
            badge: undefined,
        });
        
        // Add Group Management for system_admin users
        items.push({
            title: 'Group Management',
            href: route('admin.groups.index'),
            icon: Users2,
            badge: undefined,
        });
    }
    
    if (user?.permissions?.quick_actions && Array.isArray(user.permissions.quick_actions)) {
        const backendItems = user.permissions.quick_actions.slice(0, 3).map((item: any) => ({
            title: item.name,
            href: route(item.route),
            icon: iconMap[item.icon] || BookOpenCheck,
            badge: undefined,
        }));
        items = [...items, ...backendItems];
    }
    
    return items;
});

// Static system navigation items (can be role-based if needed)
const systemNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
        badge: undefined,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Help Center',
        href: '/help',
        icon: BookOpen,
        badge: undefined,
    },
    {
        title: 'Support',
        href: '/support',
        icon: Folder,
        badge: undefined,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-sidebar-border/50">
        <!-- Modern Header with Enhanced Logo -->
        <SidebarHeader class="border-b border-sidebar-border/50 px-3 py-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-sidebar-accent/50 transition-colors">
                        <Link :href="route('dashboard')" class="gap-3 px-3 py-2">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- Enhanced Content with Multiple Navigation Groups -->
        <SidebarContent class="px-2 py-4">
            <!-- Main Navigation -->
            <NavMain :items="mainNavItems" />
            
            <!-- Quick Access Section -->
            <div v-if="quickAccessItems.length > 0" class="mt-6">
                <div class="px-3 py-2">
                    <h3 class="text-caption font-semibold text-sidebar-foreground/60 uppercase tracking-wider">
                        Quick Actions
                    </h3>
                </div>
                <div class="space-y-1">
                    <template v-for="item in quickAccessItems" :key="item.title">
                        <Link
                            :href="item.href"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-body-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                            :class="{
                                'bg-sidebar-accent text-sidebar-accent-foreground': item.href === page.url,
                                'text-sidebar-foreground/70 hover:text-sidebar-foreground': item.href !== page.url
                            }"
                        >
                            <component :is="item.icon" class="h-4 w-4 transition-colors" />
                            <span class="flex-1">{{ item.title }}</span>
                            <span 
                                v-if="item.badge" 
                                class="inline-flex items-center justify-center h-5 w-5 text-xs font-medium bg-primary text-primary-foreground rounded-full"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- System Section -->
            <div v-if="systemNavItems.length > 0" class="mt-6">
                <div class="px-3 py-2">
                    <h3 class="text-caption font-semibold text-sidebar-foreground/60 uppercase tracking-wider">
                        System
                    </h3>
                </div>
                <div class="space-y-1">
                    <template v-for="item in systemNavItems" :key="item.title">
                        <Link
                            :href="item.href"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-body-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                            :class="{
                                'bg-sidebar-accent text-sidebar-accent-foreground': item.href === page.url,
                                'text-sidebar-foreground/70 hover:text-sidebar-foreground': item.href !== page.url
                            }"
                        >
                            <component :is="item.icon" class="h-4 w-4 transition-colors" />
                            <span class="flex-1">{{ item.title }}</span>
                        </Link>
                    </template>
                </div>
            </div>
        </SidebarContent>

        <!-- Enhanced Footer -->
        <SidebarFooter class="border-t border-sidebar-border/50 px-2 py-4">
            <NavFooter :items="footerNavItems" />
            <div class="mt-2">
                <NavUser />
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
