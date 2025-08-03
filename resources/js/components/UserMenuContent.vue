<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <!-- Modern User Header -->
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-3 px-3 py-3 text-left bg-secondary/30 rounded-t-lg">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    
    <DropdownMenuSeparator class="bg-border/50" />
    
    <!-- Menu Items Group -->
    <DropdownMenuGroup class="py-1">
        <DropdownMenuItem :as-child="true" class="transition-smooth hover:bg-secondary/50">
            <Link class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg mx-1" :href="route('profile.edit')" prefetch as="button">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                    <Settings class="h-4 w-4 text-primary" />
                </div>
                <span>Settings</span>
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    
    <DropdownMenuSeparator class="bg-border/50" />
    
    <!-- Logout Item -->
    <div class="py-1">
        <DropdownMenuItem :as-child="true" class="transition-smooth hover:bg-destructive/10">
            <Link class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg mx-1 text-destructive hover:text-destructive" method="post" :href="route('logout')" @click="handleLogout" as="button">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-destructive/10">
                    <LogOut class="h-4 w-4" />
                </div>
                <span>Log out</span>
            </Link>
        </DropdownMenuItem>
    </div>
</template>
