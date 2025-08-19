<template>
    <div class="space-y-8">
        <!-- Introduction -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <UserPlus class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Add Team Members</h3>
            <p class="text-muted-foreground">
                Invite your team members and assign them roles. They'll receive OTP verification emails. You can add 1-25 users.
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Users List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-lg font-medium text-foreground">Team Members</Label>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addUser"
                        :disabled="users.length >= 25 || loading"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg"
                    >
                        <Plus class="h-4 w-4" />
                        Add User
                    </Button>
                </div>

                <!-- No Groups Warning -->
                <div v-if="!availableGroups.length" class="p-4 bg-info/10 border border-info/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <Info class="h-5 w-5 text-info" />
                        <div>
                            <h4 class="font-medium text-info">No Groups Available</h4>
                            <p class="text-sm text-info/80 mt-1">
                                Some user roles require group assignment. You can still create users and assign groups later.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- User Cards -->
                <div class="grid gap-4">
                    <Card 
                        v-for="(user, index) in users" 
                        :key="index"
                        class="border-border/50 hover:border-primary/30 transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-primary/10 rounded-lg">
                                        <component :is="getRoleIcon(user.user_type)" class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-foreground">
                                            {{ user.first_name && user.last_name ? `${user.first_name} ${user.last_name}` : `User ${index + 1}` }}
                                        </h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ getRoleLabel(user.user_type) }}
                                        </p>
                                    </div>
                                </div>
                                <Button
                                    v-if="users.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeUser(index)"
                                    :disabled="loading"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- First Name -->
                                <div class="space-y-2">
                                    <Label :for="`user-first-name-${index}`" class="text-sm font-medium">
                                        First Name *
                                    </Label>
                                    <Input
                                        :id="`user-first-name-${index}`"
                                        v-model="user.first_name"
                                        placeholder="e.g. John"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>

                                <!-- Last Name -->
                                <div class="space-y-2">
                                    <Label :for="`user-last-name-${index}`" class="text-sm font-medium">
                                        Last Name *
                                    </Label>
                                    <Input
                                        :id="`user-last-name-${index}`"
                                        v-model="user.last_name"
                                        placeholder="e.g. Doe"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="mb-4 space-y-2">
                                <Label :for="`user-email-${index}`" class="text-sm font-medium">
                                    Email Address *
                                </Label>
                                <Input
                                    :id="`user-email-${index}`"
                                    v-model="user.email"
                                    type="email"
                                    placeholder="john.doe@company.com"
                                    :disabled="loading"
                                    required
                                    class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                />
                                <p class="text-xs text-muted-foreground">
                                    OTP verification will be sent to this email
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Role Selection -->
                                <div class="space-y-2">
                                    <Label :for="`user-role-${index}`" class="text-sm font-medium">
                                        Role *
                                    </Label>
                                    <select
                                        :id="`user-role-${index}`"
                                        v-model="user.user_type"
                                        :disabled="loading"
                                        required
                                        class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                    >
                                        <option value="">Select Role</option>
                                        <option value="hod">Head of Department</option>
                                        <option value="booking_agent">Booking Agent</option>
                                        <option value="invitee">Invitee</option>
                                    </select>
                                </div>

                                <!-- Group Assignment -->
                                <div class="space-y-2">
                                    <Label :for="`user-group-${index}`" class="text-sm font-medium">
                                        Group {{ isGroupRequired(user.user_type) ? '*' : '(Optional)' }}
                                    </Label>
                                    <select
                                        :id="`user-group-${index}`"
                                        v-model="user.group_name"
                                        :disabled="loading || !availableGroups.length"
                                        :required="isGroupRequired(user.user_type)"
                                        :class="[
                                            'w-full h-11 px-3 rounded-xl border bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none',
                                            isGroupRequired(user.user_type) ? 'border-border/60' : 'border-border/40'
                                        ]"
                                    >
                                        <option value="">{{ isGroupRequired(user.user_type) ? 'Select Group' : 'No Group' }}</option>
                                        <option v-for="group in availableGroups" :key="group" :value="group">
                                            {{ group }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Role Information -->
                            <div v-if="user.user_type" class="mt-4 p-3 bg-muted/30 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <component :is="getRoleIcon(user.user_type)" class="h-4 w-4 text-primary" />
                                    <h5 class="text-sm font-medium text-foreground">{{ getRoleLabel(user.user_type) }}</h5>
                                    <span v-if="isGroupRequired(user.user_type)" class="text-xs bg-warning/20 text-warning px-2 py-1 rounded-md">
                                        Group Required
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ getRoleDescription(user.user_type) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Add User Hint -->
                <div 
                    v-if="users.length < 25" 
                    class="border-2 border-dashed border-border/50 rounded-xl p-6 text-center hover:border-primary/30 transition-colors cursor-pointer"
                    @click="addUser"
                >
                    <Plus class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground font-medium">Add Another User</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You can add up to {{ 25 - users.length }} more users
                    </p>
                </div>
            </div>

            <!-- Form Validation Messages -->
            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="space-y-2">
                <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <AlertCircle class="h-4 w-4 text-destructive" />
                        <h4 class="font-medium text-destructive">Please fix the following errors:</h4>
                    </div>
                    <ul class="text-sm text-destructive space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field">
                            • {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- OTP Information -->
            <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 mt-0.5 flex-shrink-0">
                        <Mail class="h-4 w-4 text-primary" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-primary mb-1">OTP Verification Process</h4>
                        <p class="text-xs text-primary/80">
                            Each user will automatically receive an OTP verification email after creation. 
                            They must verify their email address before accessing VenuePro.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-border/50">
                <div class="text-sm text-muted-foreground">
                    {{ users.length }} of 25 users added
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        @click="$emit('previous')"
                        :disabled="loading"
                        class="flex items-center gap-2 h-11 px-6 rounded-xl"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Previous
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        @click="$emit('skip')"
                        :disabled="loading"
                        class="h-11 px-6 rounded-xl"
                    >
                        Skip for Now
                    </Button>
                    <Button
                        type="submit"
                        :disabled="!isFormValid || loading"
                        class="flex items-center gap-2 h-11 px-6 rounded-xl font-medium"
                    >
                        <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
                        <ArrowRight v-else class="h-4 w-4" />
                        {{ loading ? 'Creating Users...' : 'Continue to Labels' }}
                    </Button>
                </div>
            </div>
        </form>

        <!-- Help Section -->
        <Card class="bg-muted/30 border-border/30">
            <CardContent class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 flex-shrink-0">
                        <HelpCircle class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground mb-2">User Roles & Permissions</h3>
                        <div class="space-y-3 text-sm text-muted-foreground">
                            <div>
                                <p class="font-medium text-foreground">Head of Department</p>
                                <p>Full management access to rooms and bookings. Must be assigned to a group.</p>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Booking Agent</p>
                                <p>Can create and manage bookings for others. Must be assigned to a group.</p>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Invitee</p>
                                <p>Basic access to view and book available rooms. Group assignment is optional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    UserPlus,
    Plus,
    Trash2,
    ArrowRight,
    ArrowLeft,
    LoaderCircle,
    HelpCircle,
    AlertCircle,
    Mail,
    Info,
    Crown,
    Calendar,
    User
} from 'lucide-vue-next'

import type { OnboardingUser } from '@/types'

// Emits
const emit = defineEmits<{
    next: []
    previous: []
    skip: []
}>()

// Props
interface Props {
    loading?: boolean
    existingUsers?: OnboardingUser[]
    availableGroups?: string[]
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    existingUsers: () => [],
    availableGroups: () => []
})

// Reactive state
const users = ref<OnboardingUser[]>([
    { first_name: '', last_name: '', email: '', user_type: 'invitee', group_name: '' }
])

// Form setup
const form = useForm({
    users: users.value
})

// Computed properties
const isFormValid = computed(() => {
    return users.value.length > 0 && 
           users.value.every(user => 
               user.first_name.trim() !== '' && 
               user.last_name.trim() !== '' &&
               user.email.trim() !== '' &&
               user.email.includes('@') &&
               user.user_type !== '' &&
               (!isGroupRequired(user.user_type) || user.group_name !== '')
           )
})

const loading = computed(() => props.loading || form.processing)

// Role utilities
const getRoleIcon = (role: string) => {
    const iconMap = {
        hod: Crown,
        booking_agent: Calendar,
        invitee: User
    }
    return iconMap[role as keyof typeof iconMap] || User
}

const getRoleLabel = (role: string) => {
    const labelMap = {
        hod: 'Head of Department',
        booking_agent: 'Booking Agent',
        invitee: 'Invitee'
    }
    return labelMap[role as keyof typeof labelMap] || 'Select Role'
}

const getRoleDescription = (role: string) => {
    const descriptionMap = {
        hod: 'Full management access to rooms and bookings within their group',
        booking_agent: 'Can create and manage bookings for team members and clients',
        invitee: 'Basic access to view available rooms and create personal bookings'
    }
    return descriptionMap[role as keyof typeof descriptionMap] || ''
}

const isGroupRequired = (role: string) => {
    return ['hod', 'booking_agent'].includes(role)
}

// Methods
const addUser = () => {
    if (users.value.length < 25) {
        users.value.push({ 
            first_name: '', 
            last_name: '', 
            email: '', 
            user_type: 'invitee', 
            group_name: '' 
        })
        form.users = users.value
    }
}

const removeUser = (index: number) => {
    if (users.value.length > 1) {
        users.value.splice(index, 1)
        form.users = users.value
    }
}

const submit = () => {
    // Update form data with current users
    form.users = users.value

    form.post('/onboarding/users', {
        onSuccess: () => {
            emit('next')
        },
        onError: (errors) => {
            console.error('Users creation errors:', errors)
        }
    })
}

// Initialize with existing data if provided
if (props.existingUsers && props.existingUsers.length > 0) {
    users.value = [...props.existingUsers]
    form.users = users.value
}
</script>