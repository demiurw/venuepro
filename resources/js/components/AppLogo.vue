<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 shadow-sm">
        <AppLogoIcon class="size-5 fill-current text-white" />
    </div>
    <div class="ml-3 grid flex-1 text-left">
        <span class="truncate text-sm font-bold tracking-tight text-sidebar-foreground">VenuePro</span>
        <span class="truncate text-xs font-medium text-sidebar-muted-foreground">Venue Management</span>
    </div>
</template>
