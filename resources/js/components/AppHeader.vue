<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Search, Command, Bell, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const searchQuery = ref('');

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'Help Center',
        href: '/help',
        icon: BookOpen,
    },
    {
        title: 'Support',
        href: '/support',
        icon: Folder,
    },
];

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // Implement search functionality
        console.log('Searching for:', searchQuery.value);
    }
};
</script>

<template>
    <div class="bg-background/95 backdrop-blur-sm border-b border-sidebar-border/50">
        <!-- Main Header -->
        <div class="mx-auto flex h-16 items-center px-4 lg:px-6">
            <!-- Mobile Menu -->
            <div class="lg:hidden">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <Button variant="ghost" size="icon" class="mr-3 h-10 w-10 hover:bg-sidebar-accent">
                            <Menu class="h-5 w-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[300px] p-0">
                        <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                        <div class="flex h-full flex-col">
                            <SheetHeader class="border-b border-sidebar-border/50 p-6">
                                <div class="flex items-center gap-3">
                                    <AppLogoIcon class="h-6 w-6 text-primary" />
                                    <span class="font-semibold text-lg">VenuePro</span>
                                </div>
                            </SheetHeader>
                            <div class="flex-1 overflow-auto p-6">
                                <nav class="space-y-2">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-sidebar-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-4 w-4" />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>

            <!-- Logo for Desktop -->
            <Link :href="route('dashboard')" class="hidden lg:flex items-center gap-2 mr-8">
                <AppLogo />
            </Link>

            <!-- Search Bar - Modern Design -->
            <div class="flex-1 max-w-2xl mx-4 lg:mx-8">
                <div class="relative hidden lg:block">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <Input
                        id="global-search"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search venues, bookings, users..."
                        class="pl-10 pr-12 h-10 bg-muted/30 border-muted-foreground/20 focus:bg-background focus:border-primary/40 transition-colors"
                        @keypress.enter="handleSearch"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <kbd class="hidden lg:inline-flex items-center gap-1 px-2 py-1 text-xs font-mono bg-muted rounded border">
                            <Command class="h-3 w-3" />
                            K
                        </kbd>
                    </div>
                </div>
                
                <!-- Mobile Search Button -->
                <div class="lg:hidden">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-10 w-10 hover:bg-sidebar-accent"
                    >
                        <Search class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2">
                <!-- Quick Actions -->
                <div class="hidden md:flex items-center gap-1">
                    <!-- New Booking Button -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" class="h-10 w-10 hover:bg-sidebar-accent">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>New Booking</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <!-- Notifications -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" class="h-10 w-10 hover:bg-sidebar-accent relative">
                                    <Bell class="h-4 w-4" />
                                    <span class="absolute -top-1 -right-1 h-3 w-3 bg-destructive rounded-full text-xs"></span>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Notifications</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

                <!-- Help Links -->
                <div class="hidden lg:flex items-center gap-1">
                    <template v-for="item in rightNavItems" :key="item.title">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 hover:bg-sidebar-accent">
                                        <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                            <component :is="item.icon" class="h-4 w-4" />
                                        </a>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ item.title }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </template>
                </div>

                <!-- User Menu -->
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-10 w-10 rounded-full hover:bg-sidebar-accent focus-within:ring-2 focus-within:ring-primary/20"
                        >
                            <Avatar class="h-8 w-8">
                                <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                <AvatarFallback class="bg-primary/10 text-primary font-semibold text-sm">
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-64 p-2">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div v-if="props.breadcrumbs.length > 1" class="border-t border-sidebar-border/30">
            <div class="mx-auto flex h-12 items-center px-4 lg:px-6">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
