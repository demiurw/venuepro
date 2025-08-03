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

        <!-- OTP Code Sent Confirmation -->
        <div v-if="otpSent" class="mb-6">
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <Mail class="h-5 w-5 text-green-500 mr-2" />
                    <p class="text-sm text-green-700">
                        Verification code sent to <span class="font-medium">{{ form.email }}</span>
                    </p>
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
                        :disabled="form.processing || otpSent"
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
                        :disabled="form.processing || otpSent"
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
                        :disabled="form.processing || otpSent"
                    />
                    <InputError :message="form.errors.email" />
                    <p class="text-xs text-muted-foreground">
                        {{ otpSent ? 'A verification code has been sent to this email' : 'We\'ll send an activation code to verify your account' }}
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
                        :disabled="form.processing || otpSent"
                    />
                    <InputError :message="form.errors.company_name" />
                    <p class="text-xs text-muted-foreground">
                        This will be your organization's workspace name
                    </p>
                </div>

                <!-- OTP Field (always visible) -->
                <div class="grid gap-2">
                    <Label for="otp" class="text-center">Verification Code</Label>
                    <Input
                        id="otp"
                        v-model="form.otp"
                        type="text"
                        class="text-center text-3xl tracking-[0.75em] font-mono py-6 bg-gray-50 border-2 border-gray-200 focus:border-primary focus:bg-white transition-colors"
                        placeholder="000000"
                        maxlength="6"
                        required
                        :tabindex="5"
                        autocomplete="one-time-code"
                        :disabled="form.processing"
                        @input="formatOtpInput"
                    />
                    <InputError :message="form.errors.otp" />
                    <p class="text-xs text-muted-foreground text-center">
                        Enter the 6-digit code sent to your email
                    </p>
                </div>

                <!-- Terms and Conditions -->
                <div class="grid gap-2">
                    <div class="flex items-start space-x-3">
                        <Checkbox
                            id="terms"
                            v-model="form.terms"
                            :tabindex="otpSent ? 6 : 5"
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

                <!-- Buttons Section -->
                <div class="space-y-4">
                    <!-- Get Code Button -->
                    <Button
                        v-if="!otpSent"
                        type="button"
                        @click="sendOtp"
                        class="w-full"
                        :tabindex="6"
                        :disabled="form.processing || !form.terms || !isFormValid"
                    >
                        <LoaderCircle v-if="isRequestingOtp" class="h-4 w-4 animate-spin mr-2" />
                        <Mail v-else class="h-4 w-4 mr-2" />
                        {{ isRequestingOtp ? 'Sending code...' : 'Get Code' }}
                    </Button>

                    <!-- Register Button (shown after OTP sent) -->
                    <Button
                        v-else
                        type="submit"
                        class="w-full"
                        :tabindex="7"
                        :disabled="form.processing || !form.terms || form.otp.length !== 6"
                    >
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                        <CheckCircle v-else class="h-4 w-4 mr-2" />
                        {{ form.processing ? 'Creating account...' : 'Register' }}
                    </Button>

                    <!-- Countdown Timer and Resend Button -->
                    <div v-if="otpSent" class="text-center space-y-2">
                        <div v-if="countdown > 0">
                            <p class="text-sm text-muted-foreground">
                                Code expires in {{ formattedCountdown }}
                            </p>
                        </div>
                        
                        <Button
                            v-if="canResend"
                            variant="outline"
                            size="sm"
                            @click="resendOtp"
                            :disabled="isResending"
                            class="w-full"
                        >
                            <LoaderCircle v-if="isResending" class="h-4 w-4 animate-spin mr-2" />
                            <RotateCcw v-else class="h-4 w-4 mr-2" />
                            {{ isResending ? 'Sending...' : 'Resend Code' }}
                        </Button>
                        <div v-else-if="countdown > 0" class="text-sm text-muted-foreground">
                            Wait {{ formattedCountdown }} before requesting a new code
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="route('login')"
                    class="underline underline-offset-4"
                    :tabindex="otpSent ? 8 : 7"
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
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    LoaderCircle,
    Mail,
    Shield,
    Check,
    Zap,
    CheckCircle,
    AlertCircle,
    Info,
    RotateCcw
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';

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

// Registration form - removed password fields, added company name and OTP
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    company_name: '',
    terms: false,
    otp: ''
});

// State management
const otpSent = ref(false);
const isRequestingOtp = ref(false);
const isResending = ref(false);
const countdown = ref(0);
const canResend = ref(false);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

// Computed properties
const isFormValid = computed(() => {
    return form.first_name && form.last_name && form.email && form.company_name;
});

const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Send OTP code
const sendOtp = async () => {
    if (!isFormValid.value || !form.terms) return;

    isRequestingOtp.value = true;
    
    // Create a temporary form just for OTP request
    const otpForm = useForm({
        email: form.email
    });
    
    otpForm.post('/register/send-otp', {
        preserveScroll: true,
        onSuccess: (page) => {
            // OTP sent successfully, show register button and start countdown
            otpSent.value = true;
            startCountdown();
            
            // Focus on OTP input
            setTimeout(() => {
                document.getElementById('otp')?.focus();
            }, 100);
        },
        onError: (errors) => {
            if (errors?.email) {
                form.setError('email', errors.email);
            } else {
                alert('Failed to send verification code. Please try again.');
            }
        },
        onFinish: () => {
            isRequestingOtp.value = false;
        }
    });
};

// Submit registration form with OTP
const submit = () => {
    if (!otpSent.value) {
        sendOtp();
        return;
    }

    form.post(route('register'), {
        onFinish: () => {
            // Don't reset form data in case user needs to fix validation errors
        },
        onSuccess: () => {
            // Success redirect is handled by the controller
        },
        onError: (errors) => {
            // Handle validation errors, especially OTP
            console.error('Registration validation errors:', errors);
            if (errors.otp) {
                form.otp = '';
                // Focus back to OTP input after error
                setTimeout(() => {
                    document.getElementById('otp')?.focus();
                }, 100);
            }
        }
    });
};

// Resend OTP code
const resendOtp = async () => {
    if (!canResend.value || isResending.value) return;

    isResending.value = true;
    
    // Create a temporary form just for OTP request
    const otpForm = useForm({
        email: form.email
    });
    
    otpForm.post('/register/send-otp', {
        preserveScroll: true,
        onSuccess: (page) => {
            // OTP resent successfully, clear form and restart countdown
            form.otp = '';
            form.clearErrors();
            startCountdown();
        },
        onError: (errors) => {
            alert('Failed to resend verification code. Please try again.');
        },
        onFinish: () => {
            isResending.value = false;
        }
    });
};

// Start countdown timer
const startCountdown = () => {
    countdown.value = 60; // 60 seconds
    canResend.value = false;

    if (countdownInterval) {
        clearInterval(countdownInterval);
    }

    countdownInterval = setInterval(() => {
        countdown.value--;

        if (countdown.value <= 0) {
            canResend.value = true;
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }
    }, 1000);
};

// Format OTP input to only allow numbers
const formatOtpInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/\D/g, '').slice(0, 6);
    form.otp = value;
};

// Lifecycle
onMounted(() => {
    // Any initialization logic
});

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
</script>
