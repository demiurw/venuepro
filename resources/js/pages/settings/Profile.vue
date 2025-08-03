<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-8">
                <!-- Profile Information Card -->
                <div class="card-modern p-6 bg-card">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h2 class="text-h4 font-semibold text-foreground">Profile Information</h2>
                            <p class="text-body-sm text-muted-foreground">
                                Update your name and email address. Your email will be used for notifications and account recovery.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name" class="text-body-sm font-medium text-foreground">Full Name</Label>
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        required 
                                        autocomplete="name" 
                                        placeholder="Enter your full name" 
                                        class="h-11 text-body rounded-lg border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email" class="text-body-sm font-medium text-foreground">Email Address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        autocomplete="username"
                                        placeholder="your.email@company.com"
                                        class="h-11 text-body rounded-lg border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>

                            <!-- Email Verification Notice -->
                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="p-4 bg-warning/10 border border-warning/20 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-warning/20 mt-0.5 flex-shrink-0">
                                        <svg class="h-4 w-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-body-sm font-semibold text-warning mb-1">Email Verification Required</h4>
                                        <p class="text-caption text-warning/80 mb-3">
                                            Your email address is unverified. Please verify your email to ensure account security.
                                        </p>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            as-child
                                            class="bg-warning/10 hover:bg-warning/20 border-warning/30 text-warning hover:text-warning"
                                        >
                                            <Link
                                                :href="route('verification.send')"
                                                method="post"
                                                as="button"
                                            >
                                                Resend verification email
                                            </Link>
                                        </Button>
                                        
                                        <div v-if="status === 'verification-link-sent'" class="mt-3 p-3 bg-success/10 border border-success/20 rounded-lg">
                                            <p class="text-caption text-success font-medium">
                                                ✓ Verification email sent successfully!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between pt-4 border-t border-border/50">
                                <div class="flex items-center gap-4">
                                    <Button 
                                        type="submit"
                                        :disabled="form.processing"
                                        class="h-11 px-6 rounded-lg font-medium"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                    </Button>

                                    <Transition
                                        enter-active-class="transition ease-in-out duration-300"
                                        enter-from-class="opacity-0 scale-95"
                                        leave-active-class="transition ease-in-out duration-200"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div v-show="form.recentlySuccessful" class="flex items-center gap-2 px-3 py-2 bg-success/10 rounded-lg">
                                            <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="text-body-sm text-success font-medium">Changes saved successfully!</span>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Management -->
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
