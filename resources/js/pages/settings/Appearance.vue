<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-8">
                <!-- Appearance Settings Card -->
                <div class="card-modern p-6 bg-card">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h2 class="text-h4 font-semibold text-foreground">Appearance Settings</h2>
                            <p class="text-body-sm text-muted-foreground">
                                Customize how VenuePro looks and feels. Choose between light, dark, or system theme.
                            </p>
                        </div>
                        
                        <!-- Theme Selection -->
                        <div class="space-y-4">
                            <AppearanceTabs />
                        </div>
                        
                        <!-- Additional Appearance Options -->
                        <div class="pt-6 border-t border-border/50">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-3">
                                    <h3 class="text-body font-medium text-foreground">Theme Preview</h3>
                                    <p class="text-caption text-muted-foreground">
                                        Changes are applied immediately and saved automatically.
                                    </p>
                                </div>
                                <div class="space-y-3">
                                    <h3 class="text-body font-medium text-foreground">System Integration</h3>
                                    <p class="text-caption text-muted-foreground">
                                        System mode follows your device's appearance settings.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
