<template>
    <AuthLayout>
        <div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-secondary/5">
            <div class="container mx-auto px-6 py-12">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-foreground mb-4">
                        Welcome to VenuePro
                    </h1>
                    <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                        Let's set up your venue management system with a few simple steps. This will only take a few minutes.
                    </p>
                </div>

                <!-- Progress Bar -->
                <div class="max-w-4xl mx-auto mb-12">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-muted-foreground">Step {{ currentStep }} of {{ totalSteps }}</span>
                        <span class="text-sm font-medium text-muted-foreground">{{ Math.round((currentStep / totalSteps) * 100) }}% Complete</span>
                    </div>
                    <div class="w-full bg-muted rounded-full h-3 overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-primary to-primary/80 transition-all duration-700 ease-out"
                            :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Step Navigation -->
                <div class="max-w-6xl mx-auto mb-12">
                    <div class="flex justify-center">
                        <div class="flex items-center space-x-4 md:space-x-8 overflow-x-auto pb-2">
                            <div 
                                v-for="step in steps" 
                                :key="step.id"
                                class="flex items-center flex-shrink-0"
                            >
                                <div class="flex flex-col items-center">
                                    <div 
                                        :class="[
                                            'w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                                            step.is_completed 
                                                ? 'bg-success border-success text-success-foreground' 
                                                : step.is_current
                                                    ? 'bg-primary border-primary text-primary-foreground'
                                                    : step.is_accessible
                                                        ? 'border-border text-muted-foreground hover:border-primary/50 cursor-pointer'
                                                        : 'border-muted text-muted/50 cursor-not-allowed'
                                        ]"
                                        @click="step.is_accessible && !step.is_current ? goToStep(step.id) : null"
                                    >
                                        <CheckCircle v-if="step.is_completed" class="h-6 w-6" />
                                        <span v-else class="font-semibold">{{ steps.indexOf(step) + 1 }}</span>
                                    </div>
                                    <span 
                                        :class="[
                                            'text-xs font-medium mt-2 text-center max-w-20',
                                            step.is_completed 
                                                ? 'text-success' 
                                                : step.is_current
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground'
                                        ]"
                                    >
                                        {{ step.title }}
                                    </span>
                                </div>
                                <ChevronRight 
                                    v-if="steps.indexOf(step) < steps.length - 1"
                                    class="h-4 w-4 text-muted-foreground mx-4 flex-shrink-0"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step Content -->
                <div class="max-w-4xl mx-auto">
                    <Card class="border-0 shadow-xl">
                        <CardHeader class="text-center pb-8">
                            <CardTitle class="text-2xl font-semibold flex items-center justify-center gap-3">
                                <component :is="currentStepIcon" class="h-6 w-6 text-primary" />
                                {{ currentStepData?.title }}
                            </CardTitle>
                            <p class="text-muted-foreground text-lg mt-2">
                                {{ currentStepData?.description }}
                            </p>
                        </CardHeader>
                        <CardContent>
                            <!-- Dynamic Step Component -->
                            <component 
                                :is="currentComponent" 
                                @next="handleNext"
                                @previous="handlePrevious"
                                @skip="handleSkip"
                                :loading="isLoading"
                                v-bind="getStepProps()"
                            />
                        </CardContent>
                    </Card>
                </div>

                <!-- Navigation Footer -->
                <div class="max-w-4xl mx-auto mt-8">
                    <div class="flex items-center justify-between">
                        <Button
                            v-if="currentStep > 1"
                            variant="outline"
                            @click="handlePrevious"
                            :disabled="isLoading"
                            class="flex items-center gap-2 h-11 px-6 rounded-xl"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Previous Step
                        </Button>
                        <div v-else></div>

                        <div class="flex items-center gap-3">
                            <Button
                                v-if="currentStep < totalSteps"
                                variant="outline"
                                @click="handleSkip"
                                :disabled="isLoading"
                                class="h-11 px-6 rounded-xl"
                            >
                                Skip for Now
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
    CheckCircle,
    ChevronRight,
    ArrowLeft,
    Building2,
    DoorOpen,
    Users,
    UserPlus,
    Tags
} from 'lucide-vue-next'

// Import step components
import BuildingsStep from './BuildingsStep.vue'
import RoomsStep from './RoomsStep.vue'
import GroupsStep from './GroupsStep.vue'
import UsersStep from './UsersStep.vue'
import LabelsStep from './LabelsStep.vue'

import type { OnboardingProgress } from '@/types'

interface Props {
    progress?: OnboardingProgress
    currentStep?: number
    // Props for different steps
    existingBuildings?: any[]
    existingRooms?: any[]
    availableBuildings?: string[]
    existingGroups?: any[]
    existingUsers?: any[]
    availableGroups?: string[]
    existingLabels?: any[]
}

const props = withDefaults(defineProps<Props>(), {
    currentStep: 1,
    existingBuildings: () => [],
    existingRooms: () => [],
    availableBuildings: () => [],
    existingGroups: () => [],
    existingUsers: () => [],
    availableGroups: () => [],
    existingLabels: () => []
})

// Reactive state
const isLoading = ref(false)
const currentStep = ref(props.currentStep || 1)

// Step definitions
const steps = [
    {
        id: 'buildings',
        title: 'Buildings',
        description: 'Add your organization\'s buildings and locations',
        component: 'BuildingsStep',
        icon: 'Building2',
        is_completed: false,
        is_current: currentStep.value === 1,
        is_accessible: true
    },
    {
        id: 'rooms',
        title: 'Rooms',
        description: 'Set up rooms and spaces for booking',
        component: 'RoomsStep',
        icon: 'DoorOpen',
        is_completed: false,
        is_current: currentStep.value === 2,
        is_accessible: currentStep.value >= 2
    },
    {
        id: 'groups',
        title: 'Groups',
        description: 'Create departments and teams',
        component: 'GroupsStep',
        icon: 'Users',
        is_completed: false,
        is_current: currentStep.value === 3,
        is_accessible: currentStep.value >= 3
    },
    {
        id: 'users',
        title: 'Users',
        description: 'Add team members and assign roles',
        component: 'UsersStep',
        icon: 'UserPlus',
        is_completed: false,
        is_current: currentStep.value === 4,
        is_accessible: currentStep.value >= 4
    },
    {
        id: 'labels',
        title: 'Labels',
        description: 'Create booking categories and labels',
        component: 'LabelsStep',
        icon: 'Tags',
        is_completed: false,
        is_current: currentStep.value === 5,
        is_accessible: currentStep.value >= 5
    }
]

// Computed properties
const totalSteps = computed(() => steps.length)

const currentStepData = computed(() => {
    return steps.find(step => step.is_current)
})

// Component mapping
const componentMap = {
    BuildingsStep,
    RoomsStep,
    GroupsStep,
    UsersStep,
    LabelsStep
}

const currentComponent = computed(() => {
    const stepData = currentStepData.value
    const componentName = stepData ? stepData.component : 'BuildingsStep'
    return componentMap[componentName as keyof typeof componentMap] || BuildingsStep
})

const currentStepIcon = computed(() => {
    const stepData = currentStepData.value
    const iconMap = {
        'Building2': Building2,
        'DoorOpen': DoorOpen,
        'Users': Users,
        'UserPlus': UserPlus,
        'Tags': Tags
    }
    return stepData ? iconMap[stepData.icon as keyof typeof iconMap] : Building2
})

// Methods
const getStepProps = () => {
    const stepId = steps[currentStep.value - 1]?.id
    
    switch (stepId) {
        case 'buildings':
            return {
                existingBuildings: props.existingBuildings
            }
        case 'rooms':
            return {
                existingRooms: props.existingRooms,
                availableBuildings: props.availableBuildings
            }
        case 'groups':
            return {
                existingGroups: props.existingGroups
            }
        case 'users':
            return {
                existingUsers: props.existingUsers,
                availableGroups: props.availableGroups
            }
        case 'labels':
            return {
                existingLabels: props.existingLabels
            }
        default:
            return {}
    }
}

const updateStepStates = () => {
    steps.forEach((step, index) => {
        step.is_current = index + 1 === currentStep.value
        step.is_accessible = index + 1 <= currentStep.value
        // You can add completed logic based on props.progress if available
    })
}

const goToStep = (stepId: string) => {
    const stepIndex = steps.findIndex(step => step.id === stepId)
    if (stepIndex !== -1 && stepIndex + 1 <= currentStep.value) {
        currentStep.value = stepIndex + 1
        updateStepStates()
        router.get(`/onboarding/${stepId}`)
    }
}

const handleNext = async () => {
    if (currentStep.value < totalSteps.value) {
        isLoading.value = true
        
        try {
            // Move to next step
            currentStep.value += 1
            updateStepStates()
            
            const nextStep = steps[currentStep.value - 1]
            router.get(`/onboarding/${nextStep.id}`)
        } finally {
            isLoading.value = false
        }
    } else {
        // Final step completed - go to dashboard
        router.get('/admin/dashboard')
    }
}

const handlePrevious = () => {
    if (currentStep.value > 1) {
        currentStep.value -= 1
        updateStepStates()
        
        const prevStep = steps[currentStep.value - 1]
        router.get(`/onboarding/${prevStep.id}`)
    }
}

const handleSkip = async () => {
    isLoading.value = true
    
    try {
        const currentStepId = steps[currentStep.value - 1].id
        
        // Post to skip endpoint
        await router.post(`/onboarding/skip/${currentStepId}`, {}, {
            preserveState: false,
            preserveScroll: false
        })
        
        // The backend will handle the redirect to the next step or dashboard
    } catch (error) {
        console.error('Error skipping step:', error)
    } finally {
        isLoading.value = false
    }
}

// Initialize component state
onMounted(() => {
    updateStepStates()
})
</script>