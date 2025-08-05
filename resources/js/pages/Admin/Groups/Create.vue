<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Heading 
                    title="Create New Group" 
                    description="Create a new group to organize team members in your VenuePro workspace"
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

            <!-- Form Card -->
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Group Information
                    </CardTitle>
                    <p class="text-sm text-muted-foreground">
                        Provide the basic details for your new group. You can add members after creation.
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
                                placeholder="Enter group name (e.g., Marketing Team, Project Alpha)"
                                :disabled="form.processing"
                                class="h-11 rounded-xl border-border/60 focus:border-primary/60 focus:ring-primary/20"
                            />
                            <InputError :message="form.errors.name" />
                            <p class="text-caption text-muted-foreground">
                                Choose a descriptive name that clearly identifies the group's purpose
                            </p>
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
                            <p class="text-caption text-muted-foreground">
                                Optional: Provide additional context about this group's role and objectives
                            </p>
                        </div>

                        <!-- Group Settings -->
                        <div class="space-y-4">
                            <Label class="text-body-sm font-medium text-foreground">
                                Group Settings
                            </Label>
                            
                            <!-- Status -->
                            <div class="space-y-2">
                                <Label for="status" class="text-sm font-medium text-foreground">
                                    Initial Status
                                </Label>
                                <select 
                                    id="status"
                                    v-model="form.status"
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
                                        <p class="text-xs text-muted-foreground">Group is visible and members can be added immediately</p>
                                    </div>
                                    <div class="p-3 rounded-lg border border-border/30 bg-muted/20">
                                        <h4 class="text-sm font-medium text-foreground mb-1">Inactive</h4>
                                        <p class="text-xs text-muted-foreground">Group is hidden and members cannot access group features</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 mt-0.5 flex-shrink-0">
                                    <Users class="h-4 w-4 text-primary" />
                                </div>
                                <div>
                                    <h4 class="text-body-sm font-semibold text-primary mb-1">Next Steps</h4>
                                    <p class="text-caption text-primary/80">
                                        After creating the group, you'll be able to add members from your organization 
                                        and assign different roles (member, manager, admin) to control access levels.
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
                                <Users v-else class="h-4 w-4" />
                                {{ form.processing ? 'Creating Group...' : 'Create Group' }}
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
                            <h3 class="text-body font-semibold text-foreground mb-2">Group Management Tips</h3>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p>• Groups help organize users for better collaboration and access control</p>
                                <p>• You can add existing users to groups and assign different roles</p>
                                <p>• Group members can have specific permissions for venue bookings</p>
                                <p>• Inactive groups are hidden but can be reactivated at any time</p>
                                <p>• Group settings and membership can be modified after creation</p>
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
    Users,
    ArrowLeft,
    CheckCircle,
    LoaderCircle,
    HelpCircle
} from 'lucide-vue-next'

interface Props {
    message?: string
}

const props = withDefaults(defineProps<Props>(), {
    message: ''
})

// Form data
const form = useForm({
    name: '',
    description: '',
    status: 'active'
})

// Computed properties
const isFormValid = computed(() => {
    return form.name.trim().length > 0
})

// Functions
const submit = () => {
    form.post('/admin/groups', {
        onSuccess: () => {
            // Success message will be shown from the server response
            // Redirect to groups list
            setTimeout(() => {
                router.visit('/admin/groups')
            }, 2000)
        },
        onError: (errors) => {
            console.error('Group creation errors:', errors)
        }
    })
}

const goBack = () => {
    router.visit('/admin/groups')
}
</script>