<template>
    <AuthBase
        :title="pageTitle"
        :description="pageDescription"
    >
        <Head title="OTP Verification" />

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
                v-else-if="status === 'success'"
                class="p-4 bg-green-50 border border-green-200 rounded-lg"
            >
                <div class="flex items-center">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2" />
                    <p class="text-sm text-green-700">{{ message }}</p>
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
        <div v-if="isRequestStep" class="space-y-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Mail class="h-8 w-8 text-blue-600" />
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ isVerificationType ? 'Request a new verification code' : 'Enter your email to receive an OTP' }}
                </p>
            </div>

            <form @submit.prevent="submitEmailForm" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        v-model="requestForm.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="your.email@company.com"
                        :disabled="requestForm.processing"
                    />
                    <InputError :message="requestForm.errors.email" />
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="requestForm.processing"
                >
                    <LoaderCircle v-if="requestForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <Mail v-else class="h-4 w-4 mr-2" />
                    {{ requestForm.processing ? 'Sending...' : (isVerificationType ? 'Send verification code' : 'Send OTP') }}
                </Button>
            </form>

            <div class="text-center">
                <TextLink
                    :href="isVerificationType ? route('register') : route('login')"
                    class="text-sm underline underline-offset-4"
                >
                    {{ isVerificationType ? 'Back to registration' : 'Back to login' }}
                </TextLink>
            </div>
        </div>

        <!-- Verify OTP Step -->
        <div v-else-if="isVerifyStep" class="space-y-6">
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

                    <!-- Attempts Remaining -->
                    <div v-if="attempts_remaining && attempts_remaining < 3" class="text-center">
                        <p class="text-sm text-yellow-600">
                            {{ attempts_remaining }} attempt{{ attempts_remaining !== 1 ? 's' : '' }} remaining
                        </p>
                    </div>
                </div>

                <!-- Remember Me (only for login, not account verification) -->
                <div v-if="!isVerificationType" class="flex items-center justify-center">
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
                    <CheckCircle v-else class="h-4 w-4 mr-2" />
                    {{ verifyForm.processing ? 'Verifying...' : (isVerificationType ? 'Activate account' : 'Login') }}
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
                    <div v-if="isVerificationType" class="pt-2">
                        <p class="font-medium">Need help? Contact our support team.</p>
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
    ArrowLeft
} from 'lucide-vue-next';

// Props from the server
interface Props {
    email?: string;
    step?: string;
    status?: string;
    message?: string;
    expires_at?: string;
    attempts_remaining?: number;
    verification_type?: string;
    user_id?: number;
}

const props = withDefaults(defineProps<Props>(), {
    step: 'request',
    status: '',
    message: '',
    attempts_remaining: 3,
    verification_type: 'login'
});

// Forms
const requestForm = useForm({
    email: props.email || ''
});

const verifyForm = useForm({
    email: props.email || '',
    otp_code: '',
    remember: false
});

// State
const currentStep = ref(props.step);
const countdown = ref(0);
const canResend = ref(true);
const isResending = ref(false);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

// Computed properties
const isRequestStep = computed(() => currentStep.value === 'request');
const isVerifyStep = computed(() => currentStep.value === 'verify');
const isVerificationType = computed(() => props.verification_type === 'account_activation');
const displayEmail = computed(() => verifyForm.email || requestForm.email);

const pageTitle = computed(() => {
    if (isVerificationType.value) {
        return isRequestStep.value ? 'Verify Your Account' : 'Enter Verification Code';
    }
    return isRequestStep.value ? 'Login with OTP' : 'Enter Your Code';
});

const pageDescription = computed(() => {
    if (isVerificationType.value) {
        return isRequestStep.value
            ? 'Complete your account setup by verifying your email address'
            : 'Enter the verification code sent to your email to activate your account';
    }
    return isRequestStep.value
        ? 'Enter your email address to receive a secure login code'
        : 'Enter the 6-digit code sent to your email address';
});

const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Methods
const submitEmailForm = () => {
    const route_name = isVerificationType.value ? 'verification.generate' : 'otp.generate';

    requestForm.post(route(route_name), {
        onSuccess: () => {
            currentStep.value = 'verify';
            verifyForm.email = requestForm.email;
            startCountdown();
        },
        onError: (errors) => {
            console.error('Email form errors:', errors);
        }
    });
};

const submitOtpForm = () => {
    const route_name = isVerificationType.value ? 'verification.verify.otp' : 'otp.verify';

    verifyForm.post(route(route_name), {
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
        const route_name = isVerificationType.value ? 'verification.generate' : 'otp.resend';

        const response = await fetch(route(route_name), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                email: verifyForm.email
            })
        });

        const data = await response.json();

        if (response.ok && data.success !== false) {
            verifyForm.otp_code = '';
            verifyForm.clearErrors();
            startCountdown();

            // Show success message
            // You might want to use a toast notification here
        } else {
            alert(data.message || 'Failed to resend verification code. Please try again.');
        }
    } catch (error) {
        console.error('Resend error:', error);
        alert('Failed to resend verification code. Please try again.');
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
    requestForm.clearErrors();
};

// Format OTP input to only allow numbers
const formatOtpInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/\D/g, '').slice(0, 6);
    verifyForm.otp_code = value;
};

// Lifecycle
onMounted(() => {
    if (isVerifyStep.value && props.expires_at) {
        startCountdown();
    }

    // Auto-focus OTP input if we're in verify step
    if (isVerifyStep.value) {
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
