<template>
    <div class="space-y-8">
        <!-- Introduction -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <Building2 class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Add Your Buildings</h3>
            <p class="text-muted-foreground">
                Start by adding the buildings or locations where you have rooms to manage. You can add between 1-10 buildings.
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Buildings List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-lg font-medium text-foreground">Buildings</Label>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addBuilding"
                        :disabled="buildings.length >= 10 || loading"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg"
                    >
                        <Plus class="h-4 w-4" />
                        Add Building
                    </Button>
                </div>

                <!-- Building Cards -->
                <div class="grid gap-6">
                    <Card 
                        v-for="(building, index) in buildings" 
                        :key="index"
                        class="border-border/50 hover:border-primary/30 transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-primary/10 rounded-lg">
                                        <Building2 class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-foreground">Building {{ index + 1 }}</h4>
                                        <p class="text-sm text-muted-foreground">Enter building details</p>
                                    </div>
                                </div>
                                <Button
                                    v-if="buildings.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeBuilding(index)"
                                    :disabled="loading"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <!-- Building Name and Description -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="space-y-2">
                                    <Label :for="`building-name-${index}`" class="text-sm font-medium">
                                        Building Name *
                                    </Label>
                                    <Input
                                        :id="`building-name-${index}`"
                                        v-model="building.name"
                                        placeholder="e.g. Main Office Building"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label :for="`building-description-${index}`" class="text-sm font-medium">
                                        Description (Optional)
                                    </Label>
                                    <Input
                                        :id="`building-description-${index}`"
                                        v-model="building.description"
                                        placeholder="Brief description of this building"
                                        :disabled="loading"
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>
                            </div>

                            <!-- Address Section -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium text-foreground border-b border-border/30 pb-2">
                                    Address Information
                                </h5>

                                <!-- Address Line 1 and 2 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label :for="`address-line1-${index}`" class="text-sm font-medium">
                                            Street Address *
                                        </Label>
                                        <Input
                                            :id="`address-line1-${index}`"
                                            v-model="building.address_line1"
                                            placeholder="e.g. 123 Business Street"
                                            :disabled="loading"
                                            required
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="`address-line2-${index}`" class="text-sm font-medium">
                                            Address Line 2 (Optional)
                                        </Label>
                                        <Input
                                            :id="`address-line2-${index}`"
                                            v-model="building.address_line2"
                                            placeholder="e.g. Suite 100, Floor 2"
                                            :disabled="loading"
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                        />
                                    </div>
                                </div>

                                <!-- City and Country -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label :for="`city-${index}`" class="text-sm font-medium">
                                            City *
                                        </Label>
                                        <Input
                                            :id="`city-${index}`"
                                            v-model="building.city"
                                            placeholder="e.g. New York"
                                            :disabled="loading"
                                            required
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="`country-${index}`" class="text-sm font-medium">
                                            Country *
                                        </Label>
                                        <Select 
                                            :model-value="getCountrySelectValue(building)"
                                            @update:model-value="setCountrySelectValue(building, $event)"
                                            :disabled="loading"
                                            required
                                        >
                                            <SelectTrigger class="h-11 rounded-xl border-border/60 focus:border-primary/60">
                                                <SelectValue placeholder="Select Country" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem 
                                                    v-for="country in countries" 
                                                    :key="country.id" 
                                                    :value="country.id.toString()"
                                                >
                                                    {{ country.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <!-- State and Postal Code -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label :for="`state-${index}`" class="text-sm font-medium">
                                            State/Province (Optional)
                                        </Label>
                                        <Select 
                                            :model-value="getStateSelectValue(building)"
                                            @update:model-value="setStateSelectValue(building, $event)"
                                            :disabled="loading || !getStatesForCountry(building.country_id).length"
                                        >
                                            <SelectTrigger class="h-11 rounded-xl border-border/60 focus:border-primary/60">
                                                <SelectValue placeholder="Select State" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem 
                                                    v-for="state in getStatesForCountry(building.country_id)" 
                                                    :key="state.id" 
                                                    :value="state.id.toString()"
                                                >
                                                    {{ state.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="`postal-code-${index}`" class="text-sm font-medium">
                                            Postal Code (Optional)
                                        </Label>
                                        <Input
                                            :id="`postal-code-${index}`"
                                            v-model="building.postal_code"
                                            placeholder="e.g. 10001"
                                            :disabled="loading"
                                            class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                        />
                                    </div>
                                </div>

                                <!-- Timezone -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label :for="`timezone-${index}`" class="text-sm font-medium">
                                            Timezone (Optional)
                                        </Label>
                                        <Select 
                                            v-model="building.timezone"
                                            :disabled="loading"
                                        >
                                            <SelectTrigger class="h-11 rounded-xl border-border/60 focus:border-primary/60">
                                                <SelectValue placeholder="Select Timezone" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="UTC">UTC (Coordinated Universal Time)</SelectItem>
                                                <SelectItem value="America/New_York">Eastern Time (ET)</SelectItem>
                                                <SelectItem value="America/Chicago">Central Time (CT)</SelectItem>
                                                <SelectItem value="America/Denver">Mountain Time (MT)</SelectItem>
                                                <SelectItem value="America/Los_Angeles">Pacific Time (PT)</SelectItem>
                                                <SelectItem value="Europe/London">GMT (Greenwich Mean Time)</SelectItem>
                                                <SelectItem value="Europe/Paris">CET (Central European Time)</SelectItem>
                                                <SelectItem value="Asia/Tokyo">JST (Japan Standard Time)</SelectItem>
                                                <SelectItem value="Australia/Sydney">AEDT (Australian Eastern Daylight Time)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Add Building Hint -->
                <div 
                    v-if="buildings.length < 10" 
                    class="border-2 border-dashed border-border/50 rounded-xl p-6 text-center hover:border-primary/30 transition-colors cursor-pointer"
                    @click="addBuilding"
                >
                    <Plus class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground font-medium">Add Another Building</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You can add up to {{ 10 - buildings.length }} more buildings
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
                    {{ buildings.length }} of 10 buildings added
                </div>

                <div class="flex items-center gap-3">
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
                        {{ loading ? 'Saving Buildings...' : 'Continue to Rooms' }}
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
                        <h3 class="font-semibold text-foreground mb-2">Tips for Adding Buildings</h3>
                        <div class="space-y-2 text-sm text-muted-foreground">
                            <p>• Use clear, descriptive names that your team will recognize</p>
                            <p>• Provide complete address information for accurate location identification</p>
                            <p>• Select the appropriate timezone for each building location</p>
                            <p>• You can always add more buildings later from your dashboard</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Building2,
    Plus,
    Trash2,
    ArrowRight,
    LoaderCircle,
    HelpCircle,
    AlertCircle
} from 'lucide-vue-next'

import type { OnboardingBuilding, Country, State } from '@/types'

// Emits
const emit = defineEmits<{
    next: []
    skip: []
}>()

// Props
interface Props {
    loading?: boolean
    existingBuildings?: OnboardingBuilding[]
    countries?: Country[]
    states?: State[]
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    existingBuildings: () => [],
    countries: () => [],
    states: () => []
})

// Reactive state
const buildings = ref<OnboardingBuilding[]>([
    { 
        name: '', 
        description: '',
        address_line1: '', 
        address_line2: '',
        city: '', 
        state_id: undefined,
        country_id: 1, // Default to first country (US)
        postal_code: '',
        timezone: 'UTC'
    }
])

// Form setup
const form = useForm({
    buildings: buildings.value
})

// Computed properties
const isFormValid = computed(() => {
    return buildings.value.length > 0 && 
           buildings.value.every(building => 
               building.name.trim() !== '' && 
               building.address_line1.trim() !== '' &&
               building.city.trim() !== '' &&
               building.country_id > 0
           )
})

const loading = computed(() => props.loading || form.processing)

// Helper functions for Select component v-model conversion
const getCountrySelectValue = (building: OnboardingBuilding) => {
    return building.country_id?.toString() || ''
}

const setCountrySelectValue = (building: OnboardingBuilding, value: string | number | boolean | null | undefined) => {
    if (value && (typeof value === 'string' || typeof value === 'number')) {
        building.country_id = typeof value === 'string' ? parseInt(value) : value
    } else {
        building.country_id = 1
    }
}

const getStateSelectValue = (building: OnboardingBuilding) => {
    return building.state_id?.toString() || ''
}

const setStateSelectValue = (building: OnboardingBuilding, value: string | number | boolean | null | undefined) => {
    if (value && (typeof value === 'string' || typeof value === 'number')) {
        building.state_id = typeof value === 'string' ? parseInt(value) : value
    } else {
        building.state_id = undefined
    }
}

// Methods
const addBuilding = () => {
    if (buildings.value.length < 10) {
        buildings.value.push({ 
            name: '', 
            description: '',
            address_line1: '', 
            address_line2: '',
            city: '', 
            state_id: undefined,
            country_id: 1, // Default to first country
            postal_code: '',
            timezone: 'UTC'
        })
        form.buildings = buildings.value
    }
}

const removeBuilding = (index: number) => {
    if (buildings.value.length > 1) {
        buildings.value.splice(index, 1)
        form.buildings = buildings.value
    }
}

const getStatesForCountry = (countryId: number | null): State[] => {
    if (!countryId) return []
    return props.states.filter(state => state.country_id === countryId)
}

const submit = () => {
    // Update form data with current buildings
    form.buildings = buildings.value

    form.post('/onboarding/buildings', {
        onSuccess: () => {
            emit('next')
        },
        onError: (errors) => {
            console.error('Buildings creation errors:', errors)
        }
    })
}

// Watch for country changes to reset state selection
watch(buildings, (newBuildings) => {
    newBuildings.forEach((building) => {
        if (building.state_id && !getStatesForCountry(building.country_id).some(state => state.id === building.state_id)) {
            building.state_id = undefined
        }
    })
}, { deep: true })

// Debug: Log props
console.log('BuildingsStep props:', {
    countries: props.countries,
    states: props.states,
    existingBuildings: props.existingBuildings
})

// Initialize with existing data if provided
if (props.existingBuildings && props.existingBuildings.length > 0) {
    buildings.value = [...props.existingBuildings]
    form.buildings = buildings.value
}
</script>