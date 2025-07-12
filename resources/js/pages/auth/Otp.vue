<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

interface Props {
    email?: string
    step?: string
    status?: string
    message?: string
    expires_at?: string
    attempts_remaining?: number
}

const props = withDefaults(defineProps<Props>(), {
    step: 'request',
    status: '',
    message: '',
    attempts_remaining: 3
})

// Forms
const requestForm = useForm({
    email: props.email || ''
})

const verifyForm = useForm({
    email: props.email || '',
    otp_code: '',
    remember: false
})

// State
const currentStep = ref(props.step)
const countdown = ref(0)
const canResend = ref(true)
const isResending = ref(false)
let countdownInterval: NodeJS.Timeout | null = null

// Computed
const isRequestStep = computed(() => currentStep.value === 'request')
const isVerifyStep = computed(() => currentStep.value === 'verify')
const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60)
    const seconds = countdown.value % 60
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
})

// Methods
const submitEmailForm = () => {
    requestForm.post(route('otp.generate'), {
        onSuccess: (page) => {
            currentStep.value = 'verify'
            verifyForm.email = requestForm.email
            startCountdown()
        }
    })
}

const submitOtpForm = () => {
    verifyForm.post(route('otp.verify'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            if (errors.otp_code) {
                verifyForm.otp_code = ''
            }
        }
    })
}

const resendOtp = async () => {
    if (!canResend.value || isResending.value) return

    isResending.value = true

    try {
        const response = await fetch(route('otp.resend'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                email: verifyForm.email
            })
        })

        const data = await response.json()

        if (data.success) {
            verifyForm.otp_code = ''
            startCountdown()
        } else {
            alert(data.message || 'Failed to resend OTP')
        }
    } catch (error) {
        alert('Failed to resend OTP. Please try again.')
    } finally {
        isResending.value = false
    }
}

const startCountdown = () => {
    countdown.value = 120 // 2 minutes
    canResend.value = false

    if (countdownInterval) {
        clearInterval(countdownInterval)
    }

    countdownInterval = setInterval(() => {
        countdown.value--

        if (countdown.value <= 0) {
            canResend.value = true
            if (countdownInterval) {
                clearInterval(countdownInterval)
                countdownInterval = null
            }
        }
    }, 1000)
}

const goBack = () => {
    currentStep.value = 'request'
    verifyForm.clearErrors()
    verifyForm.otp_code = ''
}

// Format OTP input
const formatOtpInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    const value = input.value.replace(/\D/g, '').slice(0, 6)
    verifyForm.otp_code = value
}

// Lifecycle
onMounted(() => {
    if (isVerifyStep.value && props.expires_at) {
        startCountdown()
    }
})

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval)
    }
})
</script>

<template>
    <Head title="Login with OTP" />

    <AuthLayout>
        <div class="max-w-md mx-auto">
            <!-- Request OTP Step -->
            <div v-if="isRequestStep" class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Login with OTP
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Enter your email address to receive a one-time password
                    </p>
                </div>

                <form @submit.prevent="submitEmailForm" class="space-y-4">
                    <div>
                        <InputLabel for="email" value="Email Address" />
                        <TextInput
                            id="email"
                            v-model="requestForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            autocomplete="email"
                        />
                        <InputError class="mt-2" :message="requestForm.errors.email" />
                    </div>

                    <PrimaryButton
                        :class="{ 'opacity-25': requestForm.processing }"
                        :disabled="requestForm.processing"
                        class="w-full"
                    >
                        {{ requestForm.processing ? 'Sending...' : 'Send OTP' }}
                    </PrimaryButton>
                </form>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"
                    >
                        Back to login
                    </Link>
                </div>
            </div>

            <!-- Verify OTP Step -->
            <div v-else-if="isVerifyStep" class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Enter Verification Code
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        We sent a 6-digit code to
                        <span class="font-medium">{{ verifyForm.email }}</span>
                    </p>
                </div>

                <form @submit.prevent="submitOtpForm" class="space-y-4">
                    <div>
                        <InputLabel for="otp_code" value="Verification Code" />
                        <TextInput
                            id="otp_code"
                            v-model="verifyForm.otp_code"
                            type="text"
                            class="mt-1 block w-full text-center text-2xl tracking-widest"
                            placeholder="000000"
                            maxlength="6"
                            required
                            autofocus
                            @input="formatOtpInput"
                        />
                        <InputError class="mt-2" :message="verifyForm.errors.otp_code" />

                        <div v-if="attempts_remaining && attempts_remaining < 3" class="mt-2">
                            <p class="text-sm text-amber-600">
                                {{ attempts_remaining }} attempt{{ attempts_remaining !== 1 ? 's' : '' }} remaining
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="verifyForm.remember"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>

                    <PrimaryButton
                        :class="{ 'opacity-25': verifyForm.processing }"
                        :disabled="verifyForm.processing || verifyForm.otp_code.length !== 6"
                        class="w-full"
                    >
                        {{ verifyForm.processing ? 'Verifying...' : 'Verify Code' }}
                    </PrimaryButton>
                </form>

                <div class="space-y-3">
                    <div class="text-center">
                        <button
                            v-if="canResend"
                            @click="resendOtp"
                            :disabled="isResending"
                            class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 disabled:opacity-50"
                        >
                            {{ isResending ? 'Sending...' : 'Resend code' }}
                        </button>
                        <p v-else class="text-sm text-gray-500">
                            Resend available in {{ formattedCountdown }}
                        </p>
                    </div>

                    <div class="text-center">
                        <SecondaryButton @click="goBack" class="text-sm">
                            Use different email
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
