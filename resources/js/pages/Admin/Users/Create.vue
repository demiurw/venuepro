<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    title="Create New User" 
                    description="Add a new user to your VenuePro organization"
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

            <!-- Form Card -->
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <UserPlus class="h-5 w-5 text-primary" />
                        User Information
                    </CardTitle>
                    <p class="text-sm text-muted-foreground">
                        Fill in the details for the new user. An OTP verification will be sent automatically.
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
                                An OTP verification will be sent to this email address
                            </p>
                        </div>

                        <!-- Role Selection -->
                        <div class="space-y-2">
                            <Label for="role" class="text-body-sm font-medium text-foreground">
                                Role *
                            </Label>
                            <select 
                                id="user_type"
                                v-model="form.user_type"
                                required
                                :disabled="form.processing"
                                class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                            >
                                <option value="">Select a role</option>
                                <option value="hod">Head of Department</option>
                                <option value="booking_agent">Booking Agent</option>
                                <option value="invitee">Invitee</option>
                            </select>
                            <InputError :message="form.errors.user_type" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                                <div class="p-3 rounded-lg border border-border/30 bg-muted/20 relative">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-foreground mb-1">Head of Department</h4>
                                            <p class="text-xs text-muted-foreground">Full management access to rooms and bookings</p>
                                        </div>
                                        <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded-md font-medium">Group Required</span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-lg border border-border/30 bg-muted/20 relative">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-foreground mb-1">Booking Agent</h4>
                                            <p class="text-xs text-muted-foreground">Can create and manage bookings for others</p>
                                        </div>
                                        <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded-md font-medium">Group Required</span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-lg border border-border/30 bg-muted/20 relative">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-foreground mb-1">Invitee</h4>
                                            <p class="text-xs text-muted-foreground">Basic access to view and book available rooms</p>
                                        </div>
                                        <span class="text-xs bg-muted text-muted-foreground px-2 py-1 rounded-md">Group Optional</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Group Selection (conditional) -->
                        <div v-if="showGroupSelection" class="space-y-2">
                            <Label for="group_id" class="text-body-sm font-medium text-foreground">
                                Group Assignment {{ isGroupRequired ? '*' : '' }}
                            </Label>
                            <select 
                                id="group_id"
                                v-model="form.group_id"
                                :required="isGroupRequired"
                                :disabled="form.processing"
                                :class="[
                                    'w-full h-11 px-3 rounded-xl border bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none',
                                    isGroupRequired ? 'border-border/60' : 'border-border/40'
                                ]"
                            >
                                <option value="">{{ isGroupRequired ? 'Select a group' : 'No group assignment' }}</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                    <span v-if="group.description"> - {{ group.description }}</span>
                                </option>
                            </select>
                            <InputError :message="form.errors.group_id" />
                            <div v-if="isGroupRequired" class="p-3 bg-warning/10 border border-warning/20 rounded-lg">
                                <p class="text-sm text-warning font-medium">
                                    ⚠️ Group assignment is required for {{ form.user_type.replace('_', ' ') }} role
                                </p>
                            </div>
                            <p class="text-caption text-muted-foreground">
                                {{ getGroupSelectionHelpText() }}
                            </p>
                        </div>

                        <!-- OTP Info -->
                        <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 mt-0.5 flex-shrink-0">
                                    <Mail class="h-4 w-4 text-primary" />
                                </div>
                                <div>
                                    <h4 class="text-body-sm font-semibold text-primary mb-1">Automatic OTP Verification</h4>
                                    <p class="text-caption text-primary/80">
                                        When you create this user, VenuePro will automatically send an OTP verification 
                                        code to their email address. They'll need to verify their account before accessing the system.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-3 pt-4 border-t border-border/50">
                            <Button
                                type="submit"
                                class="flex items-center gap-2 h-11 px-6 rounded-xl font-medium flex-1 md:flex-none"
                                :disabled="form.processing || !isFormValid"
                            >
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                <UserPlus v-else class="h-4 w-4" />
                                {{ form.processing ? 'Creating User...' : 'Create User & Send OTP' }}
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

            <!-- Help Section -->
            <Card class="max-w-2xl bg-muted/30">
                <CardContent class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 flex-shrink-0">
                            <HelpCircle class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-body font-semibold text-foreground mb-2">Need Help?</h3>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p>• Users will receive an OTP verification email immediately after creation</p>
                                <p>• They must verify their email before accessing VenuePro</p>
                                <p>• Head of Department and Booking Agent roles require group assignment</p>
                                <p>• Group assignment for Invitees is optional but recommended for organization</p>
                                <p>• Role permissions can be changed later in the user management section</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
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
    UserPlus,
    ArrowLeft,
    CheckCircle,
    LoaderCircle,
    Mail,
    HelpCircle
} from 'lucide-vue-next'

interface Group {
    id: number
    name: string
    member_count: number
    status: string
}

interface RoleRequirement {
    group_required: boolean
}

interface Props {
    message?: string
    allowedRoles?: string[]
    groups?: Group[]
    roleRequirements?: Record<string, RoleRequirement>
}

const props = withDefaults(defineProps<Props>(), {
    message: '',
    allowedRoles: () => [],
    groups: () => [],
    roleRequirements: () => ({})
})

// Form data
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    user_type: '',
    group_id: ''
})

// Computed properties
const isGroupRequired = computed(() => {
    return props.roleRequirements?.[form.user_type]?.group_required ?? false
})

const isFormValid = computed(() => {
    const basicValidation = form.first_name && 
           form.last_name && 
           form.email && 
           form.user_type &&
           form.email.includes('@')
    
    // If group is required for this role, ensure group is selected
    if (isGroupRequired.value) {
        return basicValidation && form.group_id
    }
    
    return basicValidation
})

const showGroupSelection = computed(() => {
    // Show group selection if there are groups available and the role can have group membership
    return form.user_type && props.groups && props.groups.length > 0
})

// Functions
const getGroupSelectionHelpText = () => {
    if (isGroupRequired.value) {
        const requiredTexts = {
            'hod': 'Head of Department users must be assigned to a group to manage their team and resources',
            'booking_agent': 'Booking Agents must be assigned to a group to handle bookings for specific departments'
        }
        return requiredTexts[form.user_type as keyof typeof requiredTexts] || 'This role requires group assignment'
    } else {
        const optionalTexts = {
            'invitee': 'Group assignment is optional for invitees - they can book any available rooms'
        }
        return optionalTexts[form.user_type as keyof typeof optionalTexts] || 'Group assignment is optional for this role'
    }
}

const submit = () => {
    form.post('/admin/users', {
        onSuccess: () => {
            // Success message will be shown from the server response
            // Redirect to users list
            setTimeout(() => {
                router.visit('/admin/users')
            }, 2000)
        },
        onError: (errors) => {
            console.error('User creation errors:', errors)
        }
    })
}

const goBack = () => {
    router.visit('/admin/users')
}
</script>