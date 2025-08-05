<template>
    <AuthBase
        :title="pageTitle"
        :description="pageDescription"
    >
        <Head title="Account Verification" />

        <!-- Status Messages - Modern Design -->
        <div v-if="status || message" class="mb-6">
            <Alert
                v-if="status === 'success'"
                variant="success"
                class="border-success/30"
            >
                <CheckCircle class="h-4 w-4" />
                <AlertTitle>Verification Successful</AlertTitle>
                <AlertDescription>{{ message || 'Your account has been successfully verified!' }}</AlertDescription>
            </Alert>

            <Alert
                v-else-if="status === 'error'"
                variant="destructive"
                class="border-destructive/30"
            >
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Verification Failed</AlertTitle>
                <AlertDescription>{{ message || 'The verification code is invalid or expired. Please try again.' }}</AlertDescription>
            </Alert>

            <Alert
                v-else-if="status === 'expired'"
                variant="warning"
                class="border-warning/30"
            >
                <Clock class="h-4 w-4" />
                <AlertTitle>Code Expired</AlertTitle>
                <AlertDescription>{{ message || 'Your verification code has expired. Please request a new one.' }}</AlertDescription>
            </Alert>

            <Alert
                v-else-if="status === 'rate_limited'"
                variant="warning"
                class="border-warning/30"
            >
                <AlertTriangle class="h-4 w-4" />
                <AlertTitle>Too Many Attempts</AlertTitle>
                <AlertDescription>{{ message || 'Too many failed attempts. Please wait before trying again.' }}</AlertDescription>
            </Alert>

            <Alert
                v-else-if="message"
                variant="info"
                class="border-primary/30"
            >
                <Info class="h-4 w-4" />
                <AlertDescription>{{ message }}</AlertDescription>
            </Alert>
        </div>

        <!-- Request OTP Step - Modern Design -->
        <div v-if="currentStep === 'request'" class="space-y-6">
            <!-- Icon and Description -->
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-primary/20">
                    <Mail class="h-7 w-7 text-primary" />
                </div>
                <p class="text-body-sm text-muted-foreground">
                    We'll send a verification code to activate your account
                </p>
            </div>

            <!-- Email Form -->
            <form @submit.prevent="submitEmailForm" class="space-y-5">
                <div class="space-y-2">
                    <Label for="email" class="text-body-sm font-medium text-foreground">Email Address</Label>
                    <Input
                        id="email"
                        v-model="emailForm.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="your.email@company.com"
                        :disabled="emailForm.processing"
                        class="h-12 text-body rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                    />
                    <InputError :message="emailForm.errors.email" />
                </div>

                <Button
                    type="submit"
                    class="w-full h-12 rounded-xl font-medium transition-smooth"
                    :disabled="emailForm.processing"
                >
                    <LoaderCircle v-if="emailForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <Mail v-else class="h-4 w-4 mr-2" />
                    {{ emailForm.processing ? 'Sending...' : 'Send verification code' }}
                </Button>
            </form>

            <!-- Help Section -->
            <Alert variant="info" class="border-primary/20">
                <ShieldCheck class="h-4 w-4" />
                <AlertTitle>Account Activation Required</AlertTitle>
                <AlertDescription>
                    You'll receive a 6-digit verification code via email to complete your account setup.
                </AlertDescription>
            </Alert>

            <!-- Back to Login Link -->
            <div class="text-center pt-2">
                <TextLink
                    :href="route('login')"
                    class="text-body-sm text-primary hover:text-primary/80 font-medium transition-colors"
                >
                    Back to login
                </TextLink>
            </div>
        </div>

        <!-- Verify OTP Step - Modern Design -->
        <div v-else-if="currentStep === 'verify'" class="space-y-6">
            <!-- Header Section -->
            <div class="text-center">
                <div class="w-16 h-16 bg-success/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-success/20">
                    <ShieldCheck class="h-7 w-7 text-success" />
                </div>
                <h3 class="text-lg font-semibold text-foreground mb-2">Check your email</h3>
                <p class="text-body-sm text-muted-foreground">
                    We sent a 6-digit verification code to
                </p>
                <p class="text-body font-semibold text-foreground mt-1">{{ displayEmail }}</p>

                <!-- Expiration Timer -->
                <div v-if="countdown > 0" class="mt-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-muted/50 rounded-full">
                        <div class="w-2 h-2 bg-primary rounded-full animate-pulse"></div>
                        <p class="text-caption text-muted-foreground">
                            Code expires in {{ formattedCountdown }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- OTP Form -->
            <form @submit.prevent="submitOtpForm" class="space-y-6">
                <div class="space-y-3">
                    <Label for="otp_code" class="text-body-sm font-medium text-foreground text-center block">
                        Enter Verification Code
                    </Label>
                    <Input
                        id="otp_code"
                        v-model="verifyForm.otp_code"
                        type="text"
                        class="text-center text-2xl tracking-[0.5em] font-mono h-14 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
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
                    <div v-if="attemptsRemaining && attemptsRemaining < 3" class="text-center">
                        <Alert variant="warning" class="text-center">
                            <AlertTriangle class="h-4 w-4" />
                            <AlertDescription>
                                {{ attemptsRemaining }} attempt{{ attemptsRemaining !== 1 ? 's' : '' }} remaining
                            </AlertDescription>
                        </Alert>
                    </div>
                </div>

                <!-- Verify Button -->
                <Button
                    type="submit"
                    class="w-full h-12 rounded-xl font-medium transition-smooth"
                    :disabled="verifyForm.processing || verifyForm.otp_code.length !== 6"
                >
                    <LoaderCircle v-if="verifyForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <CheckCircle v-else class="h-4 w-4 mr-2" />
                    {{ verifyForm.processing ? 'Verifying...' : 'Activate account' }}
                </Button>
            </form>

            <!-- Resend OTP Section -->
            <div class="text-center space-y-3">
                <div v-if="!canResend && countdown > 0" class="text-body-sm text-muted-foreground">
                    Wait {{ formattedCountdown }} before requesting a new code
                </div>

                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    @click="resendOtp"
                    :disabled="isResending || !canResend"
                    class="w-full h-11 rounded-xl border-border/60 hover:border-border transition-smooth"
                >
                    <LoaderCircle v-if="isResending" class="h-4 w-4 animate-spin mr-2" />
                    <RotateCcw v-else class="h-4 w-4 mr-2" />
                    {{ isResending ? 'Sending...' : 'Send new code' }}
                </Button>

                <div class="pt-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="goBack"
                        :disabled="verifyForm.processing"
                        class="text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Change email address
                    </Button>
                </div>
            </div>
        </div>

        <!-- Success Step - Modern Design -->
        <div v-else-if="currentStep === 'success'" class="space-y-6 text-center">
            <div class="w-20 h-20 bg-success/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-success/20">
                <CheckCircle class="h-10 w-10 text-success" />
            </div>
            
            <div class="space-y-2">
                <h3 class="text-xl font-semibold text-foreground">Account Activated!</h3>
                <p class="text-body text-muted-foreground max-w-md mx-auto">
                    Your account has been successfully verified. You can now access all features of VenuePro.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <Button
                    @click="goToDashboard"
                    class="flex-1 h-12 rounded-xl font-medium transition-smooth"
                >
                    <Home class="h-4 w-4 mr-2" />
                    Go to Dashboard
                </Button>
                <Button
                    @click="goToLogin"
                    variant="outline"
                    class="flex-1 h-12 rounded-xl font-medium transition-smooth border-border/60 hover:border-border"
                >
                    <LogIn class="h-4 w-4 mr-2" />
                    Sign In
                </Button>
            </div>
        </div>

        <!-- Help Text - Modern Design -->
        <div class="mt-8 pt-6 border-t border-border/50">
            <div class="text-center">
                <h4 class="text-body-sm font-semibold text-foreground mb-3">Having trouble?</h4>
                <div class="space-y-2 text-caption text-muted-foreground">
                    <p>• Check your spam/junk folder</p>
                    <p>• Make sure your email address is correct</p>
                    <p>• The code expires in 10 minutes</p>
                    <div class="pt-3">
                        <TextLink
                            href="mailto:support@venuepro.com"
                            class="text-body-sm text-primary hover:text-primary/80 font-medium transition-colors"
                        >
                            Contact support for help
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </AuthBase>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import TextLink from '@/components/TextLink.vue';
import InputError from '@/components/InputError.vue';
import {
    LoaderCircle,
    Mail,
    ShieldCheck,
    CheckCircle,
    AlertCircle,
    Info,
    RotateCcw,
    ArrowLeft,
    Home,
    LogIn,
    Clock,
    AlertTriangle
} from 'lucide-vue-next';

// Props from the server
interface Props {
    email?: string;
    step?: string;
    status?: string;
    message?: string;
    expiresAt?: string;
    attemptsRemaining?: number;
    verificationType?: string;
    userId?: number;
}

const props = withDefaults(defineProps<Props>(), {
    step: 'request',
    status: '',
    message: '',
    attemptsRemaining: 3,
    verificationType: 'account_activation'
});

// Forms
const emailForm = useForm({
    email: props.email || ''
});

const verifyForm = useForm({
    email: props.email || '',
    otp_code: '',
    verification_type: props.verificationType
});

// State
const currentStep = ref(props.step);
const countdown = ref(0);
const canResend = ref(true);
const isResending = ref(false);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

// Computed properties
const displayEmail = computed(() => verifyForm.email || emailForm.email);

const pageTitle = computed(() => {
    switch (currentStep.value) {
        case 'request':
            return 'Verify Your Account';
        case 'verify':
            return 'Enter Verification Code';
        case 'success':
            return 'Account Activated';
        default:
            return 'Account Verification';
    }
});

const pageDescription = computed(() => {
    switch (currentStep.value) {
        case 'request':
            return 'Complete your account setup by verifying your email address';
        case 'verify':
            return 'Enter the verification code sent to your email to activate your account';
        case 'success':
            return 'Your account has been successfully verified and activated';
        default:
            return 'Verify your account to access VenuePro';
    }
});

const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Methods
const submitEmailForm = () => {
    emailForm.post(route('verification.send'), {
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
    verifyForm.post(route('verification.verify'), {
        onSuccess: (page) => {
            // Check if we should show success state or let server handle redirect
            if (page.props.status === 'verification_success') {
                currentStep.value = 'success';
            }
            // For all other cases, let the server handle the redirect via standard Inertia response
            // Don't override server routing decisions with hardcoded dashboard redirect
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
        const response = await fetch(route('verification.resend'), {
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
        } else {
            // Handle error - you might want to show a toast notification instead
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
    emailForm.clearErrors();
};

// Format OTP input to only allow numbers
const formatOtpInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/\D/g, '').slice(0, 6);
    verifyForm.otp_code = value;
};

// Navigation methods for success state
const goToDashboard = () => {
    router.visit(route('dashboard'));
};

const goToLogin = () => {
    router.visit(route('login'));
};

// Lifecycle
onMounted(() => {
    if (currentStep.value === 'verify' && props.expiresAt) {
        startCountdown();
    }

    // Auto-focus OTP input if we're in verify step
    if (currentStep.value === 'verify') {
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