<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- Modern Auth Layout inspired by Calendly's clean design -->
    <div class="min-h-screen bg-gradient-to-br from-background via-background to-secondary/30">
        <!-- Main Content -->
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
            <!-- Auth Card Container -->
            <div class="w-full max-w-lg">
                <!-- Auth Card -->
                <div class="glass rounded-2xl p-8 shadow-strong backdrop-blur-xl border border-border/50">
                    <!-- Logo Section -->
                    <div class="mb-8 text-center">
                        <Link :href="route('dashboard')" class="inline-flex flex-col items-center gap-3 group transition-transform hover:scale-105">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 border border-primary/20 group-hover:bg-primary/15 transition-colors">
                                <AppLogoIcon class="h-7 w-7 text-primary" />
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-lg font-semibold text-foreground">VenuePro</span>
                                <span class="text-xs font-medium text-muted-foreground tracking-wider uppercase">Venue Management</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Title Section -->
                    <div class="mb-8 text-center space-y-2">
                        <h1 class="text-h3 text-foreground font-semibold">{{ title }}</h1>
                        <p v-if="description" class="text-body-sm text-muted-foreground">{{ description }}</p>
                    </div>

                    <!-- Form Content -->
                    <div class="space-y-6">
                        <slot />
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-caption text-muted-foreground">
                        Secure venue management platform
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
