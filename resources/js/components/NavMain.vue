<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-0 py-0">
        <SidebarGroupLabel class="px-3 py-2 text-caption font-semibold text-sidebar-foreground/60 uppercase tracking-wider">
            Main Navigation
        </SidebarGroupLabel>
        <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton 
                    as-child 
                    :is-active="item.href === page.url" 
                    :tooltip="item.title"
                    class="px-3 py-2 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors group"
                >
                    <Link :href="item.href" class="flex items-center gap-3">
                        <component :is="item.icon" class="h-4 w-4 transition-colors" />
                        <span class="flex-1">{{ item.title }}</span>
                        <span 
                            v-if="item.badge" 
                            class="inline-flex items-center justify-center h-5 w-5 text-xs font-medium bg-primary text-primary-foreground rounded-full"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
