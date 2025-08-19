<template>
    <Index 
        :currentStep="1"
        :progress="progress"
        :existingBuildings="existingBuildings"
        :existingRooms="[]"
        :availableBuildings="[]"
        :existingGroups="[]"
        :existingUsers="[]"
        :availableGroups="[]"
        :existingLabels="[]"
    />
</template>

<script setup lang="ts">
import Index from './Index.vue'
import type { OnboardingProgress, OnboardingBuilding } from '@/types'

interface Props {
    progress?: OnboardingProgress
    existingBuildings?: OnboardingBuilding[]
    currentStep?: string
    nextStep?: string
}

withDefaults(defineProps<Props>(), {
    progress: undefined,
    existingBuildings: () => [],
    currentStep: 'buildings',
    nextStep: 'rooms'
})
</script>