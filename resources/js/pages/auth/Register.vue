<template>
    <AuthBase
        title="Create your VenuePro account"
        description="Enter your details below to create your account and start managing your venues"
    >
        <Head title="Register" />

        <!-- Status Messages -->
        <div v-if="status || message" class="mb-6">
            <div
                v-if="status === 'registration_success'"
                class="p-4 bg-green-50 border border-green-200 rounded-lg"
            >
                <div class="flex items-center">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2" />
                    <p class="text-sm text-green-700">{{ message }}</p>
                </div>
            </div>
            <div
                v-else-if="status === 'registration_partial'"
                class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
            >
                <div class="flex items-center">
                    <AlertCircle class="h-5 w-5 text-yellow-500 mr-2" />
                    <p class="text-sm text-yellow-700">{{ message }}</p>
                </div>
            </div>
            <div
                v-else-if="message"
                class="p-4 bg-blue-50 border border-blue-200 rounded-lg"
            >
                <div class="flex items-center">
                    <Info class="h-5 w-5 text-blue-500 mr-2" />
                    <p class="text-sm text-blue-700">{{ message }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- First Name -->
                <div class="grid gap-2">
                    <Label for="first_name">First Name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        v-model="form.first_name"
                        placeholder="Enter your first name"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.first_name" />
                </div>

                <!-- Last Name -->
                <div class="grid gap-2">
                    <Label for="last_name">Last Name</Label>
                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        v-model="form.last_name"
                        placeholder="Enter your last name"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.last_name" />
                </div>

                <!-- Email Address -->
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="3"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="your.email@company.com"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.email" />
                    <p class="text-xs text-muted-foreground">
                        You'll receive a verification code at this email address
                    </p>
                </div>

                <!-- Company Name -->
                <div class="grid gap-2">
                    <Label for="company_name">Company Name</Label>
                    <Input
                        id="company_name"
                        type="text"
                        required
                        :tabindex="4"
                        autocomplete="organization"
                        v-model="form.company_name"
                        placeholder="Your company or organization name"
                        :disabled="form.processing"
                    />
                    <InputError :message="form.errors.company_name" />
                    <p class="text-xs text-muted-foreground">
                        This will be your organization's workspace name
                    </p>
                </div>

                <!-- Terms and Conditions -->
                <div class="grid gap-2">
                    <div class="flex items-start space-x-3">
                        <Checkbox
                            id="terms"
                            v-model="form.terms"
                            :tabindex="5"
                            :disabled="form.processing"
                            required
                        />
                        <div class="grid gap-1.5 leading-none">
                            <Label
                                for="terms"
                                class="text-sm font-normal leading-relaxed cursor-pointer"
                            >
                                I agree to the
                                <TextLink
                                    href="/terms"
                                    target="_blank"
                                    class="underline underline-offset-4 hover:text-primary"
                                >
                                    Terms of Service
                                </TextLink>
                                and
                                <TextLink
                                    href="/privacy"
                                    target="_blank"
                                    class="underline underline-offset-4 hover:text-primary"
                                >
                                    Privacy Policy
                                </TextLink>
                            </Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.terms" />
                </div>

                <!-- Authentication Info Box -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <Shield class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <h4 class="text-sm font-medium text-blue-900">Secure OTP Authentication</h4>
                            <p class="text-xs text-blue-700 mt-1">
                                VenuePro uses One-Time Password (OTP) verification for enhanced security.
                                No passwords to remember or manage!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="6"
                    :disabled="form.processing || !form.terms"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    <Mail v-else class="h-4 w-4 mr-2" />
                    {{ form.processing ? 'Creating account...' : 'Create account & send verification' }}
                </Button>
            </div>

            <!-- Login Link -->
            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="route('login')"
                    class="underline underline-offset-4"
                    :tabindex="7"
                >
                    Sign in
                </TextLink>
            </div>
        </form>

        <!-- Security Features Footer -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <Check class="h-4 w-4 text-green-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900">Passwordless</p>
                        <p class="text-xs text-gray-500">No passwords to forget</p>
                    </div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <Zap class="h-4 w-4 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900">Quick Setup</p>
                        <p class="text-xs text-gray-500">Ready in 60 seconds</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthBase>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    LoaderCircle,
    Mail,
    Shield,
    Check,
    Zap,
    CheckCircle,
    AlertCircle,
    Info
} from 'lucide-vue-next';

// Props from the server
interface Props {
    status?: string;
    message?: string;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = withDefaults(defineProps<Props>(), {
    status: '',
    message: '',
});

// Registration form - removed password fields, added company name
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    company_name: '',
    terms: false,
});

// Submit registration form
const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            // Don't reset form data in case user needs to fix validation errors
            // The controller will redirect to OTP page on success
        },
        onSuccess: () => {
            // Success redirect is handled by the controller
            // User will be redirected to OTP verification page
        },
        onError: (errors) => {
            // Handle validation errors
            console.error('Registration validation errors:', errors);
        }
    });
};
</script>
