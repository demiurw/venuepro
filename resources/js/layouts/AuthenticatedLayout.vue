<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route(user.dashboard_route)" class="text-xl font-bold">
                                VenuePro
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <NavigationMenuLink
                                v-for="item in user.navigation_menu"
                                :key="item.route"
                                :href="route(item.route)"
                                :active="route().current(item.route)"
                            >
                                {{ item.name }}
                            </NavigationMenuLink>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- User Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Quick Actions (if any) -->
                        <div v-if="user.permissions.quick_actions && Array.isArray(user.permissions.quick_actions) && user.permissions.quick_actions.length > 0" class="mr-4 flex items-center space-x-2">
                            <Link
                                v-for="action in user.permissions.quick_actions.slice(0, 2)"
                                :key="action.route"
                                :href="route(action.route)"
                                class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {{ action.name }}
                            </Link>
                        </div>

                        <DropdownMenu align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        {{ user.full_name }}
                                        <span class="text-xs text-gray-400 ml-1">
                                            ({{ user.user_type_display_name }})
                                        </span>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <!-- User Info Section -->
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <div class="text-sm font-medium text-gray-900">{{ user.full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ user.user_type_display_name }}</div>
                                    <div v-if="user.company" class="text-xs text-gray-400">{{ user.company.name }}</div>
                                </div>

                                <!-- Navigation for mobile-like dropdown -->
                                <div class="py-1">
                                    <DropdownMenuItem :href="route('profile.edit')">
                                        Profile Settings
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        v-if="user.permissions.can_view_reports"
                                        :href="route(user.user_type === 'system_admin' ? 'admin.reports' : user.user_type === 'hod' ? 'hod.reports' : 'user.dashboard')"
                                    >
                                        Reports
                                    </DropdownMenuItem>
                                </div>

                                <div class="border-t border-gray-100"></div>

                                <!-- Logout -->
                                <div class="py-1">
                                    <DropdownMenuItem :href="route('logout')" method="post" as="button">
                                        Log Out
                                    </DropdownMenuItem>
                                </div>
                            </template>
                        </DropdownMenu>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <Link
                            v-for="item in user.navigation_menu"
                            :key="item.route"
                            :href="route(item.route)"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="
                                route().current(item.route)
                                    ? 'border-indigo-400 text-indigo-700 bg-indigo-50'
                                    : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                            "
                        >
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Mobile User Menu -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ user.full_name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ user.email }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ user.user_type_display_name }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition duration-150 ease-in-out"
                            >
                                Profile
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition duration-150 ease-in-out"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative mx-4 mt-4">
            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
        </div>

        <div v-if="$page.props.flash.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mx-4 mt-4">
            <span class="block sm:inline">{{ $page.props.flash.error }}</span>
        </div>

        <div v-if="$page.props.flash.warning" class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded relative mx-4 mt-4">
            <span class="block sm:inline">{{ $page.props.flash.warning }}</span>
        </div>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DropdownMenu from '@/components/ui/dropdown-menu/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/dropdown-menu/DropdownMenuItem.vue';
import NavigationMenuLink from '@/components/ui/navigation-menu/NavigationMenuLink.vue';
// Import User from your new global types file (if it's not self-contained in `inertia-props.d.ts`)
import type { User } from '@/types/inertia-props'; // Assuming you put User interface here

// The `usePage()` hook will now correctly infer its type from the global `PageProps`
// which you have augmented to be `AppPageProps`.
const page = usePage();

// Now, `page.props.auth.user` is correctly typed as `User | null`
const user = computed(() => page.props.auth.user as User); // Use `as User` if you are sure user is not null here

const showingNavigationDropdown = ref(false);
</script>
