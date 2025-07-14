<template>
    <AuthBase
        title="Welcome back"
        description="Enter your email address to receive a secure login code"
    >
        <Head title="Login" />

        <!-- Status Messages -->
        <div v-if="status || message" class="mb-6">
            <div
                v-if="status === 'success'"
                class="p-4 bg-green-50 border border-green-200 rounded-lg"
            >
                <div class="flex items-center">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2" />
                    <p class="text-sm text-green-700">{{ message || status }}</p>
                </div>
            </div>
            <div
                v-else-if="status === 'error'"
                class="p-4 bg-red-50 border border-red-200 rounded-lg"
            >
                <div class="flex items-center">
                    <AlertCircle class="h-5 w-5 text-red-500 mr-2" />
                    <p class="text-sm text-red-700">{{ message || 'An error occurred' }}</p>
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

        <!-- Request OTP Step -->
        <div v-if="currentStep === 'request'" class="space-y-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Mail class="h-8 w-8 text-blue-600" />
                </div>
                <p class="text-sm text-muted-foreground">
                    We'll send a secure verification code to your email
                </p>
            </div>

            <form @submit.prevent="submitEmailForm" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        v-model="emailForm.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="your.email@company.com"
                        :disabled="emailForm.processing"
                    />
                    <InputError :message="emailForm.errors.email" />
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="emailForm.processing"
                >
                    <LoaderCircle v-if="emailForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <Mail v-else class="h-4 w-4 mr-2" />
                    {{ emailForm.processing ? 'Sending...' : 'Send login code' }}
                </Button>
            </form>

            <!-- OTP Benefits -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start space-x-3">
                    <Shield class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                    <div>
                        <h4 class="text-sm font-medium text-green-900">Secure & Passwordless</h4>
                        <p class="text-xs text-green-700 mt-1">
                            No passwords to remember. Just enter your email and we'll send you a secure verification code.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <TextLink
                    :href="route('register')"
                    class="text-sm underline underline-offset-4"
                >
                    Don't have an account? Sign up
                </TextLink>
            </div>
        </div>

        <!-- Verify OTP Step -->
        <div v-else-if="currentStep === 'verify'" class="space-y-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Shield class="h-8 w-8 text-green-600" />
                </div>
                <p class="text-sm text-muted-foreground">
                    We sent a 6-digit code to
                    <span class="font-medium text-foreground">{{ displayEmail }}</span>
                </p>

                <!-- Expiration Timer -->
                <div v-if="countdown > 0" class="mt-2">
                    <p class="text-xs text-muted-foreground">
                        Code expires in {{ formattedCountdown }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitOtpForm" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="otp_code" class="text-center">Verification Code</Label>
                    <Input
                        id="otp_code"
                        v-model="verifyForm.otp_code"
                        type="text"
                        class="text-center text-2xl tracking-[0.5em] font-mono"
                        placeholder="000000"
                        maxlength="6"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        :disabled="verifyForm.processing"
                        @input="formatOtpInput"
                    />
                    <InputError :message="verifyForm.errors.otp_code" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="remember"
                            v-model="verifyForm.remember"
                            :disabled="verifyForm.processing"
                        />
                        <Label for="remember" class="text-sm">Remember me</Label>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="verifyForm.processing || verifyForm.otp_code.length !== 6"
                >
                    <LoaderCircle v-if="verifyForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <LogIn v-else class="h-4 w-4 mr-2" />
                    {{ verifyForm.processing ? 'Verifying...' : 'Login' }}
                </Button>
            </form>

            <!-- Resend OTP -->
            <div class="text-center space-y-2">
                <div v-if="!canResend && countdown > 0" class="text-sm text-muted-foreground">
                    Wait {{ formattedCountdown }} before requesting a new code
                </div>

                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    @click="resendOtp"
                    :disabled="isResending || !canResend"
                    class="w-full"
                >
                    <LoaderCircle v-if="isResending" class="h-4 w-4 animate-spin mr-2" />
                    <RotateCcw v-else class="h-4 w-4 mr-2" />
                    {{ isResending ? 'Sending...' : 'Send new code' }}
                </Button>

                <div class="pt-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="goBack"
                        :disabled="verifyForm.processing"
                    >
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Change email address
                    </Button>
                </div>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-8 pt-6 border-t border-border">
            <div class="text-center">
                <h4 class="text-sm font-medium text-foreground mb-2">Having trouble?</h4>
                <div class="space-y-1 text-xs text-muted-foreground">
                    <p>• Check your spam/junk folder</p>
                    <p>• Make sure your email address is correct</p>
                    <p>• The code expires in 10 minutes</p>
                    <div class="pt-2">
                        <TextLink
                            :href="route('login.help')"
                            class="font-medium"
                        >
                            Need more help?
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </AuthBase>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import TextLink from '@/components/TextLink.vue';
import InputError from '@/components/InputError.vue';
import {
    LoaderCircle,
    Mail,
    Shield,
    CheckCircle,
    AlertCircle,
    Info,
    RotateCcw,
    ArrowLeft,
    LogIn
} from 'lucide-vue-next';

// Props from the server
interface Props {
    canResetPassword?: boolean;
    status?: string;
    message?: string;
    authMethod?: string;
    otpStep?: string;
    otpEmail?: string;
}

const props = withDefaults(defineProps<Props>(), {
    canResetPassword: false,
    status: '',
    message: '',
    authMethod: 'otp',
    otpStep: 'request',
    otpEmail: '',
});

// Forms
const emailForm = useForm({
    email: props.otpEmail || ''
});

const verifyForm = useForm({
    email: props.otpEmail || '',
    otp_code: '',
    remember: false
});

// State
const currentStep = ref(props.otpStep);
const countdown = ref(0);
const canResend = ref(true);
const isResending = ref(false);
let countdownInterval: ReturnType<typeof setInterval> | null = null;
// Computed properties
const displayEmail = computed(() => verifyForm.email || emailForm.email);

const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Methods
const submitEmailForm = () => {
    emailForm.post(route('login.submit'), {
        onSuccess: () => {
            currentStep.value = 'verify';
            verifyForm.email = emailForm.email;
            startCountdown();
        },
        onError: (errors) => {
            console.error('Email form errors:', errors);
        }
    });
};

const submitOtpForm = () => {
    verifyForm.post(route('login.verify'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            if (errors.otp_code) {
                verifyForm.otp_code = '';
                // Focus back to input after error
                setTimeout(() => {
                    document.getElementById('otp_code')?.focus();
                }, 100);
            }
        }
    });
};

const resendOtp = async () => {
    if (!canResend.value || isResending.value) return;

    isResending.value = true;

    try {
        const response = await fetch(route('login.resend-otp'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                email: verifyForm.email
            })
        });

        if (response.ok) {
            verifyForm.otp_code = '';
            verifyForm.clearErrors();
            startCountdown();
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to resend login code. Please try again.');
        }
    } catch (error) {
        console.error('Resend error:', error);
        alert('Failed to resend login code. Please try again.');
    } finally {
        isResending.value = false;
    }
};

const startCountdown = () => {
    countdown.value = 600; // 10 minutes
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

const goBack = () => {
    currentStep.value = 'request';
    verifyForm.clearErrors();
    verifyForm.otp_code = '';
    emailForm.clearErrors();
};

// Format OTP input to only allow numbers
const formatOtpInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/\D/g, '').slice(0, 6);
    verifyForm.otp_code = value;
};

// Lifecycle
onMounted(() => {
    if (currentStep.value === 'verify' && displayEmail.value) {
        startCountdown();

        // Auto-focus OTP input if we're in verify step
        setTimeout(() => {
            document.getElementById('otp_code')?.focus();
        }, 100);
    }
});

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
</script>
