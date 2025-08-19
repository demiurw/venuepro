<template>
    <div class="space-y-8">
        <!-- Introduction -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <DoorOpen class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Set Up Your Rooms</h3>
            <p class="text-muted-foreground">
                Add the rooms and spaces that can be booked. You can add between 1-20 rooms across your buildings.
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Rooms List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Label class="text-lg font-medium text-foreground">Rooms & Spaces</Label>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addRoom"
                        :disabled="rooms.length >= 20 || loading"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg"
                    >
                        <Plus class="h-4 w-4" />
                        Add Room
                    </Button>
                </div>

                <!-- No Buildings Warning -->
                <div v-if="!availableBuildings.length" class="p-4 bg-warning/10 border border-warning/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <AlertTriangle class="h-5 w-5 text-warning" />
                        <div>
                            <h4 class="font-medium text-warning">No Buildings Available</h4>
                            <p class="text-sm text-warning/80 mt-1">
                                Please add buildings first before creating rooms, or continue without specifying buildings.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Room Cards -->
                <div class="grid gap-4">
                    <Card 
                        v-for="(room, index) in rooms" 
                        :key="index"
                        class="border-border/50 hover:border-primary/30 transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 bg-primary/10 rounded-lg">
                                        <component :is="getRoomTypeIcon(room.type)" class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-foreground">Room {{ index + 1 }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ getRoomTypeLabel(room.type) }}</p>
                                    </div>
                                </div>
                                <Button
                                    v-if="rooms.length > 1"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeRoom(index)"
                                    :disabled="loading"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                <!-- Room Name -->
                                <div class="space-y-2">
                                    <Label :for="`room-name-${index}`" class="text-sm font-medium">
                                        Room Name *
                                    </Label>
                                    <Input
                                        :id="`room-name-${index}`"
                                        v-model="room.name"
                                        placeholder="e.g. Conference Room A"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                </div>

                                <!-- Building Selection -->
                                <div class="space-y-2">
                                    <Label :for="`room-building-${index}`" class="text-sm font-medium">
                                        Building
                                    </Label>
                                    <select
                                        :id="`room-building-${index}`"
                                        v-model="room.building_name"
                                        :disabled="loading || !availableBuildings.length"
                                        class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                    >
                                        <option value="">Select Building</option>
                                        <option v-for="building in availableBuildings" :key="building" :value="building">
                                            {{ building }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Room Type -->
                                <div class="space-y-2">
                                    <Label :for="`room-type-${index}`" class="text-sm font-medium">
                                        Room Type *
                                    </Label>
                                    <select
                                        :id="`room-type-${index}`"
                                        v-model="room.type"
                                        :disabled="loading"
                                        required
                                        class="w-full h-11 px-3 rounded-xl border border-border/60 bg-background text-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none"
                                    >
                                        <option value="conference">Conference Room</option>
                                        <option value="meeting">Meeting Room</option>
                                        <option value="training">Training Room</option>
                                        <option value="auditorium">Auditorium</option>
                                        <option value="classroom">Classroom</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Capacity -->
                                <div class="space-y-2">
                                    <Label :for="`room-capacity-${index}`" class="text-sm font-medium">
                                        Capacity *
                                    </Label>
                                    <Input
                                        :id="`room-capacity-${index}`"
                                        v-model.number="room.capacity"
                                        type="number"
                                        min="1"
                                        max="1000"
                                        placeholder="e.g. 12"
                                        :disabled="loading"
                                        required
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                    <p class="text-xs text-muted-foreground">Maximum number of people</p>
                                </div>

                                <!-- Equipment -->
                                <div class="space-y-2">
                                    <Label :for="`room-equipment-${index}`" class="text-sm font-medium">
                                        Equipment (Optional)
                                    </Label>
                                    <Input
                                        :id="`room-equipment-${index}`"
                                        v-model="room.equipment"
                                        placeholder="e.g. Projector, Whiteboard"
                                        :disabled="loading"
                                        class="h-11 rounded-xl border-border/60 focus:border-primary/60"
                                    />
                                    <p class="text-xs text-muted-foreground">Available equipment</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label :for="`room-description-${index}`" class="text-sm font-medium">
                                    Description (Optional)
                                </Label>
                                <textarea
                                    :id="`room-description-${index}`"
                                    v-model="room.description"
                                    placeholder="Additional details about this room..."
                                    :disabled="loading"
                                    rows="2"
                                    class="w-full px-3 py-2 rounded-xl border border-border/60 bg-background text-foreground placeholder-muted-foreground focus:border-primary/60 focus:ring-primary/20 focus:outline-none resize-none"
                                ></textarea>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Add Room Hint -->
                <div 
                    v-if="rooms.length < 20" 
                    class="border-2 border-dashed border-border/50 rounded-xl p-6 text-center hover:border-primary/30 transition-colors cursor-pointer"
                    @click="addRoom"
                >
                    <Plus class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground font-medium">Add Another Room</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You can add up to {{ 20 - rooms.length }} more rooms
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
                    {{ rooms.length }} of 20 rooms added
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
                        {{ loading ? 'Saving Rooms...' : 'Continue to Groups' }}
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
                        <h3 class="font-semibold text-foreground mb-2">Room Setup Tips</h3>
                        <div class="space-y-2 text-sm text-muted-foreground">
                            <p>• Use descriptive names that clearly identify each room</p>
                            <p>• Set accurate capacity limits for booking constraints</p>
                            <p>• Select appropriate room types for better organization</p>
                            <p>• Include equipment information to help users choose suitable rooms</p>
                            <p>• You can add more rooms and modify details later</p>
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
    DoorOpen,
    Plus,
    Trash2,
    ArrowRight,
    ArrowLeft,
    LoaderCircle,
    HelpCircle,
    AlertCircle,
    AlertTriangle,
    Users,
    Presentation,
    GraduationCap,
    Theater,
    BookOpen,
    Settings
} from 'lucide-vue-next'

import type { OnboardingRoom } from '@/types'

// Emits
const emit = defineEmits<{
    next: []
    previous: []
    skip: []
}>()

// Props
interface Props {
    loading?: boolean
    existingRooms?: OnboardingRoom[]
    availableBuildings?: string[]
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    existingRooms: () => [],
    availableBuildings: () => []
})

// Reactive state
const rooms = ref<OnboardingRoom[]>([
    { name: '', building_name: '', capacity: 10, type: 'conference', description: '', equipment: '' }
])

// Form setup
const form = useForm({
    rooms: rooms.value
})

// Computed properties
const isFormValid = computed(() => {
    return rooms.value.length > 0 && 
           rooms.value.every(room => 
               room.name.trim() !== '' && 
               room.capacity > 0 &&
               room.type !== ''
           )
})

const loading = computed(() => props.loading || form.processing)

// Room type utilities
const getRoomTypeIcon = (type: string) => {
    const iconMap = {
        conference: Users,
        meeting: Users,
        training: Presentation,
        auditorium: Theater,
        classroom: GraduationCap,
        other: Settings
    }
    return iconMap[type as keyof typeof iconMap] || Users
}

const getRoomTypeLabel = (type: string) => {
    const labelMap = {
        conference: 'Conference Room',
        meeting: 'Meeting Room',
        training: 'Training Room',
        auditorium: 'Auditorium',
        classroom: 'Classroom',
        other: 'Other Space'
    }
    return labelMap[type as keyof typeof labelMap] || 'Room'
}

// Methods
const addRoom = () => {
    if (rooms.value.length < 20) {
        rooms.value.push({ 
            name: '', 
            building_name: '', 
            capacity: 10, 
            type: 'conference', 
            description: '', 
            equipment: '' 
        })
        form.rooms = rooms.value
    }
}

const removeRoom = (index: number) => {
    if (rooms.value.length > 1) {
        rooms.value.splice(index, 1)
        form.rooms = rooms.value
    }
}

const submit = () => {
    // Update form data with current rooms
    form.rooms = rooms.value

    form.post('/onboarding/rooms', {
        onSuccess: () => {
            emit('next')
        },
        onError: (errors) => {
            console.error('Rooms creation errors:', errors)
        }
    })
}

// Initialize with existing data if provided
if (props.existingRooms && props.existingRooms.length > 0) {
    rooms.value = [...props.existingRooms]
    form.rooms = rooms.value
}
</script>