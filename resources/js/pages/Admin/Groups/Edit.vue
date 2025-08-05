<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    :title="`Edit Group: ${group.name}`"
                    description="Update group information and settings"
                />
                <Button 
                    @click="goBack"
                    variant="outline"
                    class="flex items-center gap-2 h-10 px-4 rounded-xl"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Groups
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
                <!-- Group Info Card -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <Edit class="h-5 w-5 text-primary" />
                                Group Information
                            </CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Update the group's basic information and settings
                            </p>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Group Name -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-body-sm font-medium text-foreground">
                                        Group Name *
                                    </Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        autofocus
                                        v-model="form.name"
                                        placeholder="Enter group name"
                                        :disabled="form.processing"
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- Group Description -->
                                <div class="space-y-2">
                                    <Label for="description" class="text-body-sm font-medium text-foreground">
                                        Description
                                    </Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Describe the group's purpose, goals, or responsibilities..."
                                        :disabled="form.processing"
                                        rows="4"
                                        class="w-full px-3 py-2 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none resize-none"
                                    ></textarea>
                                    <InputError :message="form.errors.description" />
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status" class="text-body-sm font-medium text-foreground">
                                        Status *
                                    </Label>
                                    <select 
                                        id="status"
                                        v-model="form.status"
                                        required
                                        :disabled="form.processing"
                                        class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <InputError :message="form.errors.status" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                        <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                            <h4 class="text-sm font-medium text-foreground mb-1">Active</h4>
                                            <p class="text-xs text-muted-foreground">Group is visible and accessible to members</p>
                                        </div>
                                        <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                            <h4 class="text-sm font-medium text-foreground mb-1">Inactive</h4>
                                            <p class="text-xs text-muted-foreground">Group is hidden and members cannot access features</p>
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
                                        {{ form.processing ? 'Updating Group...' : 'Update Group' }}
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

                <!-- Group Details Sidebar -->
                <div class="space-y-6">
                    <!-- Group Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Group Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <span 
                                    :class="getStatusBadgeClass(group.status)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    <div :class="getStatusDotClass(group.status)" class="w-1.5 h-1.5 rounded-full"></div>
                                    {{ group.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Members</span>
                                <span class="text-sm font-medium">{{ group.member_count }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Group ID</span>
                                <span class="text-sm font-mono">{{ group.id }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Group Statistics -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Statistics</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Created</span>
                                <span class="text-sm">{{ formatDate(group.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Last Updated</span>
                                <span class="text-sm">{{ formatDate(group.updated_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Active Members</span>
                                <span class="text-sm font-medium">{{ group.active_members || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Bookings</span>
                                <span class="text-sm font-medium">{{ group.total_bookings || 0 }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                @click="manageMembers"
                                :disabled="form.processing"
                            >
                                <UserCog class="h-4 w-4 mr-2" />
                                Manage Members
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                @click="viewActivity"
                                :disabled="form.processing"
                            >
                                <Activity class="h-4 w-4 mr-2" />
                                View Activity
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start text-destructive hover:text-destructive"
                                @click="deleteGroup"
                                :disabled="form.processing"
                            >
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete Group
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Member Breakdown -->
                    <Card v-if="group.member_breakdown">
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">Member Roles</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                    <span class="text-sm text-muted-foreground">Admins</span>
                                </div>
                                <span class="text-sm font-medium">{{ group.member_breakdown.admins || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <span class="text-sm text-muted-foreground">Managers</span>
                                </div>
                                <span class="text-sm font-medium">{{ group.member_breakdown.managers || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span class="text-sm text-muted-foreground">Members</span>
                                </div>
                                <span class="text-sm font-medium">{{ group.member_breakdown.members || 0 }}</span>
                            </div>
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
    UserCog,
    Activity,
    Trash2
} from 'lucide-vue-next'

interface Group {
    id: number
    name: string
    description: string | null
    status: 'active' | 'inactive'
    member_count: number
    active_members?: number
    total_bookings?: number
    created_at: string
    updated_at: string
    member_breakdown?: {
        admins: number
        managers: number
        members: number
    }
}

interface Props {
    group: Group
    message?: string
}

const props = withDefaults(defineProps<Props>(), {
    message: ''
})

// Form data - pre-populate with group data
const form = useForm({
    name: props.group.name,
    description: props.group.description || '',
    status: props.group.status
})

// Store original values to detect changes
const originalValues = {
    name: props.group.name,
    description: props.group.description || '',
    status: props.group.status
}

// Computed properties
const isFormValid = computed(() => {
    return form.name.trim().length > 0
})

const hasChanges = computed(() => {
    return form.name !== originalValues.name ||
           form.description !== originalValues.description ||
           form.status !== originalValues.status
})

// Badge styling functions
const getStatusBadgeClass = (status: string) => {
    const classes = {
        'active': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
        'inactive': 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
    }
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300'
}

const getStatusDotClass = (status: string) => {
    const classes = {
        'active': 'bg-green-500',
        'inactive': 'bg-gray-400'
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
    form.put(`/admin/groups/${props.group.id}`, {
        onSuccess: () => {
            // Success message will be shown from the server response
            // Redirect to groups list after a brief delay
            setTimeout(() => {
                router.visit('/admin/groups')
            }, 2000)
        },
        onError: (errors) => {
            console.error('Group update errors:', errors)
        }
    })
}

const goBack = () => {
    router.visit('/admin/groups')
}

const manageMembers = () => {
    router.visit(`/admin/groups/${props.group.id}/members`)
}

const viewActivity = () => {
    // This could navigate to an activity/audit log page
    alert('Activity tracking feature coming soon!')
}

const deleteGroup = () => {
    if (confirm(`Are you sure you want to delete the group "${props.group.name}"? This action cannot be undone and will remove all ${props.group.member_count} members from the group.`)) {
        router.delete(`/admin/groups/${props.group.id}`, {
            onSuccess: () => {
                router.visit('/admin/groups')
            },
            onError: () => {
                alert('Failed to delete group. Please try again.')
            }
        })
    }
}
</script>