<template>
    <div class="space-y-8">
        <!-- Introduction -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <Users class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Create Your Groups</h3>
            <p class="text-muted-foreground">
                Set up departments, teams, or organizational groups. These help organize users and manage permissions. You can create 1-15 groups.
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Groups List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-lg font-medium text-foreground">Departments & Teams</Label>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addGroup"
                        :disabled="groups.length >= 15 || loading"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg"
                    >
                        <Plus class="h-4 w-4" />
                        Add Group
                    </Button>
                </div>

                <!-- Group Cards -->
                <div class="grid gap-4">
                    <Card 
                        v-for="(group, index) in groups" 
                        :key="index"
                        class="border-border/50 hover:border-primary/30 transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-primary/10 rounded-lg">
                                        <component :is="getGroupIcon(index)" class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-foreground">Group {{ index + 1 }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ group.name || 'Enter group name' }}
                                        </p>
                                    </div>
                                </div>
                                <Button
                                    v-if="groups.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeGroup(index)"
                                    :disabled="loading"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="space-y-4">
                                <!-- Group Name -->
                                <div class="space-y-2">
                                    <Label :for="`group-name-${index}`" class="text-sm font-medium">
                                        Group Name *
                                    </Label>
                                    <Input
                                        :id="`group-name-${index}`"
                                        v-model="group.name"
                                        placeholder="e.g. Human Resources, IT Department, Marketing Team"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Choose a clear, descriptive name for this group
                                    </p>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label :for="`group-description-${index}`" class="text-sm font-medium">
                                        Description (Optional)
                                    </Label>
                                    <textarea
                                        :id="`group-description-${index}`"
                                        v-model="group.description"
                                        placeholder="Describe the purpose, responsibilities, or scope of this group..."
                                        :disabled="loading"
                                        rows="3"
                                        class="w-full px-3 py-2 rounded-xl border border-border/60 bg-background text-foreground placeholder-muted-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none resize-none"
                                    ></textarea>
                                    <p class="text-xs text-muted-foreground">
                                        Help users understand what this group is for
                                    </p>
                                </div>
                            </div>

                            <!-- Group Examples -->
                            <div v-if="!group.name" class="mt-4 p-3 bg-muted/50 rounded-lg">
                                <h5 class="text-xs font-medium text-muted-foreground mb-2">EXAMPLE GROUPS:</h5>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="example in groupExamples"
                                        :key="example.name"
                                        type="button"
                                        @click="applyGroupExample(index, example)"
                                        class="text-xs px-3 py-1 rounded-md bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
                                    >
                                        {{ example.name }}
                                    </button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Add Group Hint -->
                <div 
                    v-if="groups.length < 15" 
                    class="border-2 border-dashed border-border/50 rounded-xl p-6 text-center hover:border-primary/30 transition-colors cursor-pointer"
                    @click="addGroup"
                >
                    <Plus class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground font-medium">Add Another Group</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You can add up to {{ 15 - groups.length }} more groups
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

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-border/50">
                <div class="text-sm text-muted-foreground">
                    {{ groups.length }} of 15 groups created
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
                        {{ loading ? 'Creating Groups...' : 'Continue to Users' }}
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
                        <h3 class="font-semibold text-foreground mb-2">Group Organization Tips</h3>
                        <div class="space-y-2 text-sm text-muted-foreground">
                            <p>• <strong>Departments:</strong> HR, Finance, IT, Marketing, Sales, Operations</p>
                            <p>• <strong>Teams:</strong> Development Team, Design Team, Project Managers</p>
                            <p>• <strong>Functions:</strong> Executives, Board Members, Consultants</p>
                            <p>• Groups help control room access and booking permissions</p>
                            <p>• You can modify groups and reassign users later</p>
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
    Users,
    Plus,
    Trash2,
    ArrowRight,
    ArrowLeft,
    LoaderCircle,
    HelpCircle,
    AlertCircle,
    Building2,
    Briefcase,
    Cpu,
    Megaphone,
    ShoppingCart,
    Settings,
    Users2,
    Zap,
    Target,
    Shield
} from 'lucide-vue-next'

import type { OnboardingGroup } from '@/types'

// Emits
const emit = defineEmits<{
    next: []
    previous: []
    skip: []
}>()

// Props
interface Props {
    loading?: boolean
    existingGroups?: OnboardingGroup[]
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    existingGroups: () => []
})

// Reactive state
const groups = ref<OnboardingGroup[]>([
    { name: '', description: '' }
])

// Form setup
const form = useForm({
    groups: groups.value
})

// Group examples for quick setup
const groupExamples = [
    { name: 'Human Resources', description: 'Manages employee relations, benefits, and organizational development' },
    { name: 'IT Department', description: 'Handles technology infrastructure, support, and development' },
    { name: 'Marketing Team', description: 'Responsible for brand promotion, campaigns, and market research' },
    { name: 'Sales Team', description: 'Manages customer relationships and revenue generation' },
    { name: 'Finance Department', description: 'Handles budgeting, accounting, and financial planning' },
    { name: 'Operations', description: 'Manages day-to-day business operations and processes' },
    { name: 'Executives', description: 'Senior leadership and decision-making team' },
    { name: 'Customer Support', description: 'Provides assistance and support to customers' }
]

// Computed properties
const isFormValid = computed(() => {
    return groups.value.length > 0 && 
           groups.value.every(group => group.name.trim() !== '')
})

const loading = computed(() => props.loading || form.processing)

// Icon selection for visual variety
const getGroupIcon = (index: number) => {
    const icons = [Users, Building2, Briefcase, Cpu, Megaphone, ShoppingCart, Settings, Users2, Zap, Target, Shield]
    return icons[index % icons.length]
}

// Methods
const addGroup = () => {
    if (groups.value.length < 15) {
        groups.value.push({ name: '', description: '' })
        form.groups = groups.value
    }
}

const removeGroup = (index: number) => {
    if (groups.value.length > 1) {
        groups.value.splice(index, 1)
        form.groups = groups.value
    }
}

const applyGroupExample = (index: number, example: { name: string; description: string }) => {
    groups.value[index].name = example.name
    groups.value[index].description = example.description
    form.groups = groups.value
}

const submit = () => {
    // Update form data with current groups
    form.groups = groups.value

    form.post('/onboarding/groups', {
        onSuccess: () => {
            emit('next')
        },
        onError: (errors) => {
            console.error('Groups creation errors:', errors)
        }
    })
}

// Initialize with existing data if provided
if (props.existingGroups && props.existingGroups.length > 0) {
    groups.value = [...props.existingGroups]
    form.groups = groups.value
}
</script>