<template>
    <Index 
        :currentStep="2"
        :progress="progress"
        :existingBuildings="[]"
        :existingRooms="existingRooms"
        :availableBuildings="availableBuildings"
        :existingGroups="[]"
        :existingUsers="[]"
        :availableGroups="[]"
        :existingLabels="[]"
    />
</template>

<script setup lang="ts">
import Index from './Index.vue'
import type { OnboardingProgress, OnboardingRoom } from '@/types'

interface Props {
    progress?: OnboardingProgress
    existingRooms?: OnboardingRoom[]
    availableBuildings?: string[]
}

withDefaults(defineProps<Props>(), {
    progress: undefined,
    existingRooms: () => [],
    availableBuildings: () => []
})
</script>