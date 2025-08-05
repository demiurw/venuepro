<template>
    <AuthBase
        title="Account Pending"
        description="Your account is pending verification"
    >
        <Head title="Account Pending" />

        <!-- Status Messages -->
        <div v-if="message" class="mb-6">
            <Alert variant="info" class="border-primary/20">
                <Info class="h-4 w-4" />
                <AlertDescription>{{ message }}</AlertDescription>
            </Alert>
        </div>

        <!-- User Status Display -->
        <div v-if="user" class="space-y-6">
            <!-- Status Icon and Title -->
            <div class="text-center">
                <div class="w-16 h-16 bg-warning/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-warning/20">
                    <Clock class="h-7 w-7 text-warning" />
                </div>
                <h3 class="text-lg font-semibold text-foreground mb-2">{{ user.status_label }}</h3>
                <p class="text-body-sm text-muted-foreground">
                    Your account for <span class="font-semibold">{{ user.email }}</span> is pending verification.
                </p>
            </div>

            <!-- Account Status Card -->
            <div class="p-6 bg-muted/50 rounded-xl border border-border/50">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning/20 flex-shrink-0">
                        <AlertTriangle class="h-5 w-5 text-warning" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-body font-semibold text-foreground mb-2">Account Verification Required</h4>
                        <p class="text-body-sm text-muted-foreground mb-4">
                            To complete your account setup and gain access to VenuePro, you need to verify your email address.
                        </p>
                        
                        <!-- Verification Status -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-warning rounded-full"></div>
                                <span class="text-caption text-muted-foreground">Status: {{ user.status_label }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-muted-foreground rounded-full"></div>
                                <span class="text-caption text-muted-foreground">Email: {{ user.email }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div v-if="user.requires_verification">
                            <Button
                                @click="startVerification"
                                class="h-11 rounded-xl font-medium transition-smooth"
                            >
                                <Mail class="h-4 w-4 mr-2" />
                                Verify My Account
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <Alert variant="info" class="border-primary/20">
                <ShieldCheck class="h-4 w-4" />
                <AlertTitle>What happens next?</AlertTitle>
                <AlertDescription class="space-y-2">
                    <p>1. Click "Verify My Account" to request a verification code</p>
                    <p>2. Check your email for a 6-digit verification code</p>
                    <p>3. Enter the code to activate your account</p>
                    <p>4. Once verified, you'll have full access to VenuePro</p>
                </AlertDescription>
            </Alert>
        </div>

        <!-- No User State -->
        <div v-else class="space-y-6 text-center">
            <div class="w-16 h-16 bg-muted/50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-border/50">
                <AlertCircle class="h-7 w-7 text-muted-foreground" />
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg font-semibold text-foreground">Account Status Unknown</h3>
                <p class="text-body text-muted-foreground max-w-md mx-auto">
                    We couldn't determine your account status. Please try logging in again.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <Button
                    @click="goToLogin"
                    class="flex-1 h-12 rounded-xl font-medium transition-smooth"
                >
                    <LogIn class="h-4 w-4 mr-2" />
                    Back to Login
                </Button>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-8 pt-6 border-t border-border/50">
            <div class="text-center">
                <h4 class="text-body-sm font-semibold text-foreground mb-3">Need help?</h4>
                <div class="space-y-2 text-caption text-muted-foreground">
                    <p>• Check your spam/junk folder for verification emails</p>
                    <p>• Make sure your email address is correct</p>
                    <p>• Verification codes expire after 10 minutes</p>
                    <div class="pt-3">
                        <TextLink
                            href="mailto:support@venuepro.com"
                            class="text-body-sm text-primary hover:text-primary/80 font-medium transition-colors"
                        >
                            Contact support for assistance
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </AuthBase>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import TextLink from '@/components/TextLink.vue';
import {
    Clock,
    AlertTriangle,
    AlertCircle,
    Info,
    Mail,
    ShieldCheck,
    LogIn
} from 'lucide-vue-next';

// Props from the server
interface Props {
    user?: {
        email: string;
        status: string;
        status_label: string;
        requires_verification: boolean;
    } | null;
    message?: string;
}

const props = withDefaults(defineProps<Props>(), {
    user: null,
    message: ''
});

// Methods
const startVerification = () => {
    if (props.user) {
        router.visit(route('verification.show', { 
            email: props.user.email,
            step: 'request' 
        }));
    }
};

const goToLogin = () => {
    router.visit(route('login'));
};
</script>