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
    Bell
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Enhanced navigation structure inspired by modern scheduling platforms
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        badge: undefined,
    },
    {
        title: 'Bookings',
        href: '/bookings',
        icon: Calendar,
        badge: '12',
    },
    {
        title: 'Venues',
        href: '/venues',
        icon: MapPin,
        badge: undefined,
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        badge: undefined,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        badge: undefined,
    },
];

const quickAccessItems: NavItem[] = [
    {
        title: 'New Booking',
        href: '/bookings/create',
        icon: BookOpenCheck,
        badge: undefined,
    },
    {
        title: 'Notifications',
        href: '/notifications',
        icon: Bell,
        badge: '3',
    },
];

const systemNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
        badge: undefined,
    },
    {
        title: 'Admin Panel',
        href: '/admin',
        icon: Shield,
        badge: undefined,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
        badge: undefined,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
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
            <div class="mt-6">
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
            <div class="mt-6">
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
