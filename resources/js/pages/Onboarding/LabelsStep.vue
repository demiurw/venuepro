<template>
    <div class="space-y-8">
        <!-- Introduction -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <Tags class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Create Resource Labels</h3>
            <p class="text-muted-foreground">
                Create custom labels to categorize and organize your company resources. These labels can be applied to buildings, rooms, users, and groups for better organization and identification. You can create 1-20 labels.
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Labels List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-lg font-medium text-foreground">Resource Labels</Label>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addLabel"
                        :disabled="labels.length >= 20 || loading"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg"
                    >
                        <Plus class="h-4 w-4" />
                        Add Label
                    </Button>
                </div>

                <!-- Label Cards -->
                <div class="grid gap-4">
                    <Card 
                        v-for="(label, index) in labels" 
                        :key="index"
                        class="border-border/50 hover:border-primary/30 transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="flex items-center justify-center w-8 h-8 rounded-lg"
                                        :style="{ backgroundColor: `${label.color}20`, color: label.color }"
                                    >
                                        <Tag class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-foreground">
                                            {{ label.name || `Label ${index + 1}` }}
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div 
                                                class="w-3 h-3 rounded-full"
                                                :style="{ backgroundColor: label.color }"
                                            ></div>
                                            <span class="text-sm text-muted-foreground">
                                                {{ label.applicable_to ? label.applicable_to.join(', ') : 'No resources selected' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    v-if="labels.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeLabel(index)"
                                    :disabled="loading"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Label Name -->
                                <div class="space-y-2">
                                    <Label :for="`label-name-${index}`" class="text-sm font-medium">
                                        Label Name *
                                    </Label>
                                    <Input
                                        :id="`label-name-${index}`"
                                        v-model="label.name"
                                        placeholder="e.g. VIP, Priority, Restricted, Department"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>

                                <!-- Color Selection -->
                                <div class="space-y-2">
                                    <Label :for="`label-color-${index}`" class="text-sm font-medium">
                                        Color *
                                    </Label>
                                    <div class="flex items-center gap-3">
                                        <Input
                                            :id="`label-color-${index}`"
                                            v-model="label.color"
                                            type="color"
                                            :disabled="loading"
                                            required
                                            class="w-12 h-11 rounded-xl border-border/60 focus:border-primary/60 p-1"
                                        />
                                        <div class="flex flex-wrap gap-2 flex-1">
                                            <button
                                                v-for="color in colorPresets"
                                                :key="color"
                                                type="button"
                                                @click="label.color = color"
                                                :class="[
                                                    'w-6 h-6 rounded-full border-2 transition-all',
                                                    label.color === color 
                                                        ? 'border-foreground scale-110' 
                                                        : 'border-border hover:border-foreground/50 hover:scale-105'
                                                ]"
                                                :style="{ backgroundColor: color }"
                                                :title="color"
                                            ></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Applicable Resources -->
                            <div class="space-y-2 mb-4">
                                <Label class="text-sm font-medium">Apply to Resources *</Label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label
                                        v-for="resourceType in ['buildings', 'rooms', 'users', 'groups']"
                                        :key="resourceType"
                                        class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-muted/50 transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="label.applicable_to?.includes(resourceType)"
                                            @change="toggleResourceType(index, resourceType)"
                                            :disabled="loading"
                                            class="rounded border-border text-primary focus:ring-primary/20"
                                        >
                                        <span class="text-sm font-medium capitalize">{{ resourceType }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label :for="`label-description-${index}`" class="text-sm font-medium">
                                    Description (Optional)
                                </Label>
                                <textarea
                                    :id="`label-description-${index}`"
                                    v-model="label.description"
                                    placeholder="Describe when this label should be used..."
                                    :disabled="loading"
                                    rows="2"
                                    class="w-full px-3 py-2 rounded-xl border border-border/60 bg-background text-foreground placeholder-muted-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none resize-none"
                                ></textarea>
                            </div>

                            <!-- Label Examples -->
                            <div v-if="!label.name" class="mt-4 p-3 bg-muted/50 rounded-lg">
                                <h5 class="text-xs font-medium text-muted-foreground mb-2">EXAMPLE LABELS:</h5>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="example in labelExamples"
                                        :key="example.name"
                                        type="button"
                                        @click="applyLabelExample(index, example)"
                                        class="text-xs px-3 py-1 rounded-md bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
                                    >
                                        {{ example.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div v-if="label.name" class="mt-4 p-3 border-2 border-dashed border-border/50 rounded-lg">
                                <h5 class="text-xs font-medium text-muted-foreground mb-2">PREVIEW:</h5>
                                <div class="flex items-center gap-2">
                                    <div 
                                        class="px-3 py-1 rounded-full text-xs font-medium border"
                                        :style="{ 
                                            backgroundColor: `${label.color}20`, 
                                            color: label.color,
                                            borderColor: `${label.color}40`
                                        }"
                                    >
                                        {{ label.name }}
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        How it will appear on resources ({{ label.applicable_to?.join(', ') || 'Select resources above' }})
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Add Label Hint -->
                <div 
                    v-if="labels.length < 20" 
                    class="border-2 border-dashed border-border/50 rounded-xl p-6 text-center hover:border-primary/30 transition-colors cursor-pointer"
                    @click="addLabel"
                >
                    <Plus class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground font-medium">Add Another Label</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You can add up to {{ 20 - labels.length }} more labels
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

            <!-- Completion Information -->
            <div class="p-4 bg-success/10 border border-success/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20 mt-0.5 flex-shrink-0">
                        <CheckCircle class="h-4 w-4 text-success" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-success mb-1">Almost Done!</h4>
                        <p class="text-xs text-success/80">
                            After creating these resource labels, your VenuePro setup will be complete. These labels will be available for organizing your buildings, rooms, users, and groups throughout the system.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-border/50">
                <div class="text-sm text-muted-foreground">
                    {{ labels.length }} of 20 labels created
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
                        @click="handleSkipAndComplete"
                        :disabled="loading"
                        class="h-11 px-6 rounded-xl"
                    >
                        Skip Labels
                    </Button>
                    <Button
                        type="submit"
                        :disabled="!isFormValid || loading"
                        class="flex items-center gap-2 h-11 px-6 rounded-xl font-medium"
                    >
                        <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
                        <CheckCircle v-else class="h-4 w-4" />
                        {{ loading ? 'Finalizing Setup...' : 'Complete Setup' }}
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
                        <h3 class="font-semibold text-foreground mb-2">Resource Label Examples</h3>
                        <div class="space-y-2 text-sm text-muted-foreground">
                            <p>• <strong>Access Levels:</strong> VIP, Priority, Restricted, Public Access</p>
                            <p>• <strong>Departments:</strong> Engineering, Marketing, HR, Finance, Executive</p>
                            <p>• <strong>Building Types:</strong> Main Office, Satellite Office, Storage, Production</p>
                            <p>• <strong>Room Categories:</strong> Conference, Training, Phone Booth, Kitchen, Lounge</p>
                            <p>• <strong>User Classifications:</strong> Executive, Manager, Staff, Contractor, Visitor</p>
                            <p>• Choose distinct colors to make labels easily recognizable across all resources</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Tags,
    Tag,
    Plus,
    Trash2,
    ArrowLeft,
    LoaderCircle,
    HelpCircle,
    AlertCircle,
    CheckCircle
} from 'lucide-vue-next'

import type { OnboardingLabel } from '@/types'

// Emits
const emit = defineEmits<{
    next: []
    previous: []
    skip: []
}>()

// Props
interface Props {
    loading?: boolean
    existingLabels?: OnboardingLabel[]
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    existingLabels: () => []
})

// Reactive state
const labels = ref<OnboardingLabel[]>([
    { name: '', color: '#3B82F6', description: '', applicable_to: [], is_active: true }
])

// Form setup
const form = useForm({
    labels: labels.value
})

// Color presets for quick selection
const colorPresets = [
    '#3B82F6', // Blue
    '#EF4444', // Red  
    '#10B981', // Green
    '#F59E0B', // Orange
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#06B6D4', // Cyan
    '#84CC16', // Lime
    '#F97316', // Orange
    '#6366F1', // Indigo
    '#14B8A6', // Teal
    '#F43F5E'  // Rose
]

// Label examples for quick setup
const labelExamples = [
    { name: 'VIP', color: '#EF4444', description: 'High-priority resources requiring special access', applicable_to: ['buildings', 'rooms', 'users'] },
    { name: 'Executive', color: '#8B5CF6', description: 'Executive-level resources and personnel', applicable_to: ['rooms', 'users', 'groups'] },
    { name: 'Public Access', color: '#10B981', description: 'Resources available for general use', applicable_to: ['buildings', 'rooms'] },
    { name: 'Engineering', color: '#3B82F6', description: 'Engineering department resources', applicable_to: ['rooms', 'users', 'groups'] },
    { name: 'Training', color: '#F59E0B', description: 'Training and educational resources', applicable_to: ['rooms', 'groups'] },
    { name: 'Restricted', color: '#DC2626', description: 'Restricted access resources', applicable_to: ['buildings', 'rooms', 'users'] },
    { name: 'Satellite Office', color: '#06B6D4', description: 'Remote office locations', applicable_to: ['buildings'] },
    { name: 'Contractor', color: '#84CC16', description: 'External contractor access', applicable_to: ['users', 'groups'] }
]

// Computed properties
const isFormValid = computed(() => {
    return labels.value.length > 0 && 
           labels.value.every(label => 
               label.name.trim() !== '' && 
               label.color !== '' &&
               label.applicable_to && label.applicable_to.length > 0
           )
})

const loading = computed(() => props.loading || form.processing)

// Methods
const addLabel = () => {
    if (labels.value.length < 20) {
        const availableColors = colorPresets.filter(color => 
            !labels.value.some(label => label.color === color)
        )
        const newColor = availableColors.length > 0 
            ? availableColors[0] 
            : colorPresets[labels.value.length % colorPresets.length]

        labels.value.push({ 
            name: '', 
            color: newColor, 
            description: '',
            applicable_to: [],
            is_active: true 
        })
        form.labels = labels.value
    }
}

const removeLabel = (index: number) => {
    if (labels.value.length > 1) {
        labels.value.splice(index, 1)
        form.labels = labels.value
    }
}

const toggleResourceType = (index: number, resourceType: string) => {
    const label = labels.value[index]
    if (!label.applicable_to) {
        label.applicable_to = []
    }
    
    const typeIndex = label.applicable_to.indexOf(resourceType as any)
    if (typeIndex > -1) {
        label.applicable_to.splice(typeIndex, 1)
    } else {
        label.applicable_to.push(resourceType as any)
    }
    form.labels = labels.value
}

const applyLabelExample = (index: number, example: { name: string; color: string; description: string; applicable_to: string[] }) => {
    labels.value[index].name = example.name
    labels.value[index].color = example.color
    labels.value[index].description = example.description
    labels.value[index].applicable_to = [...example.applicable_to] as any
    form.labels = labels.value
}

const submit = () => {
    // Update form data with current labels
    form.labels = labels.value

    form.post('/onboarding/labels', {
        onSuccess: () => {
            // This is the final step, redirect to dashboard
            router.get('/admin/dashboard')
        },
        onError: (errors) => {
            console.error('Labels creation errors:', errors)
        }
    })
}

const handleSkipAndComplete = () => {
    // Skip labels and go directly to dashboard
    router.post('/onboarding/skip/labels', {}, {
        onSuccess: () => {
            router.get('/admin/dashboard')
        }
    })
}

// Initialize with existing data if provided
if (props.existingLabels && props.existingLabels.length > 0) {
    labels.value = [...props.existingLabels]
    form.labels = labels.value
}
</script>