<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    :title="`Edit User: ${user.first_name} ${user.last_name}`"
                    description="Update user information and role permissions"
                />
                <Button 
                    @click="goBack"
                    variant="outline"
                    class="flex items-center gap-2 h-10 px-4 rounded-xl"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Users
                </Button>
            </div>

            <!-- Status Messages -->
            <div v-if="message" class="mb-6">
                <div class="p-4 bg-success/10 border border-success/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20">
                            <CheckCircle class="h-4 w-4 text-success" />
                        </div>
                        <p class="text-body-sm text-success font-medium">{{ message }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Info Card -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <Edit class="h-5 w-5 text-primary" />
                                User Information
                            </CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Update the user's basic information and role
                            </p>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- First Name -->
                                    <div class="space-y-2">
                                        <Label for="first_name" class="text-body-sm font-medium text-foreground">
                                            First Name *
                                        </Label>
                                        <Input
                                            id="first_name"
                                            type="text"
                                            required
                                            autofocus
                                            v-model="form.first_name"
                                            placeholder="Enter first name"
                                            :disabled="form.processing"
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                        />
                                        <InputError :message="form.errors.first_name" />
                                    </div>

                                    <!-- Last Name -->
                                    <div class="space-y-2">
                                        <Label for="last_name" class="text-body-sm font-medium text-foreground">
                                            Last Name *
                                        </Label>
                                        <Input
                                            id="last_name"
                                            type="text"
                                            required
                                            v-model="form.last_name"
                                            placeholder="Enter last name"
                                            :disabled="form.processing"
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                        />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                </div>

                                <!-- Email Address -->
                                <div class="space-y-2">
                                    <Label for="email" class="text-body-sm font-medium text-foreground">
                                        Email Address *
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        required
                                        v-model="form.email"
                                        placeholder="user@company.com"
                                        :disabled="form.processing"
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                    />
                                    <InputError :message="form.errors.email" />
                                    <p class="text-caption text-muted-foreground">
                                        Changing the email will require the user to verify the new address
                                    </p>
                                </div>

                                <!-- Role Selection -->
                                <div class="space-y-2">
                                    <Label for="role" class="text-body-sm font-medium text-foreground">
                                        Role *
                                    </Label>
                                    <select 
                                        id="role"
                                        v-model="form.role"
                                        required
                                        :disabled="form.processing"
                                        class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                    >
                                        <option value="">Select a role</option>
                                        <option value="Head of Department">Head of Department</option>
                                        <option value="Booking Agent">Booking Agent</option>
                                        <option value="Invitee">Invitee</option>
                                    </select>
                                    <InputError :message="form.errors.role" />
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                                        <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                            <h4 class="text-sm font-medium text-foreground mb-1">Head of Department</h4>
                                            <p class="text-xs text-muted-foreground">Full management access to rooms and bookings</p>
                                        </div>
                                        <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                            <h4 class="text-sm font-medium text-foreground mb-1">Booking Agent</h4>
                                            <p class="text-xs text-muted-foreground">Can create and manage bookings for others</p>
                                        </div>
                                        <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                            <h4 class="text-sm font-medium text-foreground mb-1">Invitee</h4>
                                            <p class="text-xs text-muted-foreground">Basic access to view and book available rooms</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="flex items-center gap-3 pt-4 border-t border-border/50">
                                    <Button
                                        type="submit"
                                        class="flex items-center gap-2 h-11 px-6 rounded-xl font-medium flex-1 md:flex-none"
                                        :disabled="form.processing || !isFormValid || !hasChanges"
                                    >
                                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                        <Save v-else class="h-4 w-4" />
                                        {{ form.processing ? 'Updating User...' : 'Update User' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="goBack"
                                        :disabled="form.processing"
                                        class="h-11 px-6 rounded-xl"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- User Details Sidebar -->
                <div class="space-y-6">
                    <!-- User Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">User Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <span 
                                    :class="getStatusBadgeClass(user.status)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    <div :class="getStatusDotClass(user.status)" class="w-1.5 h-1.5 rounded-full"></div>
                                    {{ user.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Current Role</span>
                                <span 
                                    :class="getRoleBadgeClass(user.role)"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ user.role }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">User ID</span>
                                <span class="text-sm font-mono">{{ user.id }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Activity Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Activity</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Last Active</span>
                                <span class="text-sm font-medium">{{ formatDate(user.last_active) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Created</span>
                                <span class="text-sm">{{ formatDate(user.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Bookings</span>
                                <span class="text-sm font-medium">{{ user.total_bookings || 0 }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                @click="resendOtp"
                                :disabled="form.processing"
                            >
                                <Mail class="h-4 w-4 mr-2" />
                                Resend OTP
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                @click="resetPassword"
                                :disabled="form.processing"
                            >
                                <Key class="h-4 w-4 mr-2" />
                                Reset Password
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start text-destructive hover:text-destructive"
                                @click="deactivateUser"
                                :disabled="form.processing"
                            >
                                <UserMinus class="h-4 w-4 mr-2" />
                                {{ user.status === 'active' ? 'Deactivate User' : 'Activate User' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Edit,
    ArrowLeft,
    CheckCircle,
    LoaderCircle,
    Save,
    Mail,
    Key,
    UserMinus
} from 'lucide-vue-next'

interface User {
    id: number
    first_name: string
    last_name: string
    email: string
    role: string
    status: 'active' | 'inactive' | 'pending'
    last_active: string
    created_at: string
    total_bookings?: number
}

interface Props {
    user: User
    message?: string
}

const props = withDefaults(defineProps<Props>(), {
    message: ''
})

// Form data - pre-populate with user data
const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    role: props.user.role
})

// Store original values to detect changes
const originalValues = {
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    role: props.user.role
}

// Computed properties
const isFormValid = computed(() => {
    return form.first_name && 
           form.last_name && 
           form.email && 
           form.role &&
           form.email.includes('@')
})

const hasChanges = computed(() => {
    return form.first_name !== originalValues.first_name ||
           form.last_name !== originalValues.last_name ||
           form.email !== originalValues.email ||
           form.role !== originalValues.role
})

// Badge styling functions (same as Index.vue)
const getRoleBadgeClass = (role: string) => {
    const classes = {
        'Head of Department': 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300',
        'Booking Agent': 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
        'Invitee': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
    }
    return classes[role as keyof typeof classes] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
}

const getStatusBadgeClass = (status: string) => {
    const classes = {
        'active': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
        'inactive': 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300',
        'pending': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300'
    }
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
}

const getStatusDotClass = (status: string) => {
    const classes = {
        'active': 'bg-green-500',
        'inactive': 'bg-gray-400',
        'pending': 'bg-yellow-500'
    }
    return classes[status as keyof typeof classes] || 'bg-gray-400'
}

const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMs = now.getTime() - date.getTime()
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))

    if (diffInDays === 0) {
        return 'Today'
    } else if (diffInDays === 1) {
        return 'Yesterday'
    } else if (diffInDays < 7) {
        return `${diffInDays} days ago`
    } else {
        return date.toLocaleDateString()
    }
}

// Functions
const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        onSuccess: () => {
            // Success message will be shown from the server response
            // Redirect to users list after a brief delay
            setTimeout(() => {
                router.visit('/admin/users')
            }, 2000)
        },
        onError: (errors) => {
            console.error('User update errors:', errors)
        }
    })
}

const goBack = () => {
    router.visit('/admin/users')
}

const resendOtp = () => {
    router.post(`/admin/users/${props.user.id}/resend-otp`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            alert('OTP verification email sent successfully!')
        },
        onError: () => {
            alert('Failed to send OTP. Please try again.')
        }
    })
}

const resetPassword = () => {
    if (confirm('Are you sure you want to reset this user\'s password? They will receive an email with instructions.')) {
        router.post(`/admin/users/${props.user.id}/reset-password`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Password reset email sent successfully!')
            },
            onError: () => {
                alert('Failed to send password reset email. Please try again.')
            }
        })
    }
}

const deactivateUser = () => {
    const action = props.user.status === 'active' ? 'deactivate' : 'activate'
    const confirmMessage = action === 'deactivate' 
        ? 'Are you sure you want to deactivate this user? They will lose access to VenuePro.'
        : 'Are you sure you want to activate this user? They will regain access to VenuePro.'
    
    if (confirm(confirmMessage)) {
        router.post(`/admin/users/${props.user.id}/${action}`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show updated status
                window.location.reload()
            },
            onError: () => {
                alert(`Failed to ${action} user. Please try again.`)
            }
        })
    }
}
</script>