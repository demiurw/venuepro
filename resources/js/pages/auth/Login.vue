<template>
    <AuthBase
        title="Welcome back"
        description="Enter your email address to receive a secure login code"
    >
        <Head title="Login" />

        <!-- Status Messages - Modern Design -->
        <div v-if="status || message" class="mb-6">
            <div
                v-if="status === 'success'"
                class="p-4 bg-success/10 border border-success/20 rounded-xl"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20">
                        <CheckCircle class="h-4 w-4 text-success" />
                    </div>
                    <p class="text-body-sm text-success font-medium">{{ message || status }}</p>
                </div>
            </div>
            <div
                v-else-if="status === 'error'"
                class="p-4 bg-destructive/10 border border-destructive/20 rounded-xl"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-destructive/20">
                        <AlertCircle class="h-4 w-4 text-destructive" />
                    </div>
                    <p class="text-body-sm text-destructive font-medium">{{ message || 'An error occurred' }}</p>
                </div>
            </div>
            <div
                v-else-if="status === 'account_inactive'"
                class="p-4 bg-warning/10 border border-warning/20 rounded-xl"
            >
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-warning/20 mt-0.5 flex-shrink-0">
                        <AlertCircle class="h-4 w-4 text-warning" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-body-sm font-semibold text-warning mb-1">Account Verification Required</h4>
                        <p class="text-body-sm text-warning/80 mb-3">
                            {{ message || 'Your account needs to be verified before you can sign in.' }}
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="redirectToVerification"
                            class="bg-warning/10 hover:bg-warning/20 border-warning/30 text-warning hover:text-warning"
                        >
                            <Mail class="h-4 w-4 mr-2" />
                            Verify Account
                        </Button>
                    </div>
                </div>
            </div>
            <div
                v-else-if="message"
                class="p-4 bg-primary/10 border border-primary/20 rounded-xl"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20">
                        <Info class="h-4 w-4 text-primary" />
                    </div>
                    <p class="text-body-sm text-primary font-medium">{{ message }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Account Verification - Modern Design -->
        <div v-if="props.pendingUser && currentStep === 'request'" class="mb-6">
            <div class="p-4 bg-warning/10 border border-warning/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-warning/20 mt-0.5 flex-shrink-0">
                        <AlertCircle class="h-4 w-4 text-warning" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-body-sm font-semibold text-warning mb-1">Account Verification Required</h4>
                        <p class="text-body-sm text-warning/80 mb-3">
                            Your account for <span class="font-semibold">{{ props.pendingUser.email }}</span> needs to be verified.
                            Click below to resend the activation code.
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="handlePendingVerification"
                            :disabled="emailForm.processing"
                            class="bg-warning/10 hover:bg-warning/20 border-warning/30 text-warning hover:text-warning"
                        >
                            <Mail class="h-4 w-4 mr-2" />
                            Resend verification code
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request OTP Step - Modern Design -->
        <div v-if="currentStep === 'request'" class="space-y-6">
            <!-- Icon and Description -->
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-primary/20">
                    <Mail class="h-7 w-7 text-primary" />
                </div>
                <p class="text-body-sm text-muted-foreground">
                    We'll send a secure verification code to your email
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
                    {{ emailForm.processing ? 'Sending...' : 'Send login code' }}
                </Button>
            </form>

            <!-- Social Login Options - Modern Design -->
            <div class="space-y-5">
                <!-- Elegant Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-border/50" />
                    </div>
                    <div class="relative flex justify-center text-caption uppercase tracking-wider">
                        <span class="bg-card px-4 text-muted-foreground">Or continue with</span>
                    </div>
                </div>

                <!-- Social Login Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <Button
                        variant="outline"
                        type="button"
                        @click="loginWithProvider('google')"
                        :disabled="emailForm.processing"
                        class="h-12 rounded-xl border-border/60 hover:border-border transition-smooth"
                    >
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="font-medium">Google</span>
                    </Button>

                    <Button
                        variant="outline"
                        type="button"
                        @click="loginWithProvider('microsoft')"
                        :disabled="emailForm.processing"
                        class="h-12 rounded-xl border-border/60 hover:border-border transition-smooth"
                    >
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24">
                            <path fill="#F25022" d="M1 1h10v10H1z"/>
                            <path fill="#00A4EF" d="M13 1h10v10H13z"/>
                            <path fill="#7FBA00" d="M1 13h10v10H1z"/>
                            <path fill="#FFB900" d="M13 13h10v10H13z"/>
                        </svg>
                        <span class="font-medium">Microsoft</span>
                    </Button>
                </div>
            </div>

            <!-- Security Benefits - Modern Design -->
            <div class="p-4 bg-success/10 border border-success/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20 mt-0.5 flex-shrink-0">
                        <Shield class="h-4 w-4 text-success" />
                    </div>
                    <div>
                        <h4 class="text-body-sm font-semibold text-success mb-1">Secure & Passwordless</h4>
                        <p class="text-caption text-success/80">
                            No passwords to remember. Just enter your email and we'll send you a secure verification code.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="text-center pt-2">
                <TextLink
                    :href="route('register')"
                    class="text-body-sm text-primary hover:text-primary/80 font-medium transition-colors"
                >
                    Don't have an account? Sign up
                </TextLink>
            </div>
        </div>

        <!-- Verify OTP Step - Modern Design -->
        <div v-else-if="currentStep === 'verify'" class="space-y-6">
            <!-- Header Section -->
            <div class="text-center">
                <div class="w-16 h-16 bg-success/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-success/20">
                    <Shield class="h-7 w-7 text-success" />
                </div>
                <p class="text-body-sm text-muted-foreground">
                    We sent a 6-digit code to
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
                    <Label for="otp_code" class="text-body-sm font-medium text-foreground text-center block">Verification Code</Label>
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
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-center">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="remember"
                            v-model="verifyForm.remember"
                            :disabled="verifyForm.processing"
                        />
                        <Label for="remember" class="text-body-sm text-muted-foreground">Remember me</Label>
                    </div>
                </div>

                <!-- Login Button -->
                <Button
                    type="submit"
                    class="w-full h-12 rounded-xl font-medium transition-smooth"
                    :disabled="verifyForm.processing || verifyForm.otp_code.length !== 6"
                >
                    <LoaderCircle v-if="verifyForm.processing" class="h-4 w-4 animate-spin mr-2" />
                    <LogIn v-else class="h-4 w-4 mr-2" />
                    {{ verifyForm.processing ? 'Verifying...' : 'Login' }}
                </Button>
            </form>

            <!-- Resend OTP - Modern Design -->
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
                            :href="route('login.help')"
                            class="text-body-sm text-primary hover:text-primary/80 font-medium transition-colors"
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
import { Head, useForm, router } from '@inertiajs/vue3';
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
    otpSent?: boolean;
    pendingUser?: {
        email: string;
        id: number;
    };
    flash?: {
        otp_sent?: boolean;
        email?: string;
        success?: string;
    };
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
        onSuccess: (page) => {
            // Check if OTP was sent successfully
            if (page.props.flash?.otp_sent || page.props.otp_sent || page.props.otpStep === 'verify') {
                currentStep.value = 'verify';
                verifyForm.email = page.props.otpEmail || emailForm.email;
                startCountdown();
            }
            // For all other cases, let the server handle the redirect via standard Inertia response
        },
        onError: (errors) => {
            console.error('Email form errors:', errors);
            // Let the server handle all error-based routing decisions
            // Client should not override server routing based on error messages
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

// Handle pending account verification using Inertia navigation
const handlePendingVerification = () => {
    if (props.pendingUser) {
        // Pre-fill the email and redirect to verification page
        router.visit(route('auth.verify-otp', {
            email: props.pendingUser.email,
            step: 'request'
        }));
    }
};

// Redirect to verification page using Inertia navigation
const redirectToVerification = () => {
    router.visit(route('auth.verify-otp', {
        email: emailForm.email,
        step: 'request'
    }));
};

// Social login handler
const loginWithProvider = (provider: 'google' | 'microsoft') => {
    window.location.href = route('auth.social.redirect', { provider });
};

// Lifecycle
onMounted(() => {
    // Pre-fill email if pending user exists
    if (props.pendingUser && !emailForm.email) {
        emailForm.email = props.pendingUser.email;
    }

    // Check if OTP was sent (from flash data or props) and switch to verify step
    if (props.flash?.otp_sent || props.otpSent || props.otpStep === 'verify') {
        currentStep.value = 'verify';
        if (props.flash?.email || props.otpEmail) {
            const email = props.flash?.email || props.otpEmail;
            verifyForm.email = email;
            emailForm.email = email;
        }
        startCountdown();
    }

    if (currentStep.value === 'verify' && displayEmail.value) {
        if (!countdownInterval) {
            startCountdown();
        }

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
