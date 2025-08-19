<template>
    <Index 
        :currentStep="5"
        :progress="progress"
        :existingBuildings="[]"
        :existingRooms="[]"
        :availableBuildings="[]"
        :existingGroups="[]"
        :existingUsers="[]"
        :availableGroups="[]"
        :existingLabels="existingLabels"
    />
</template>

<script setup lang="ts">
import Index from './Index.vue'
import type { OnboardingProgress, OnboardingLabel } from '@/types'

interface Props {
    progress?: OnboardingProgress
    existingLabels?: OnboardingLabel[]
}

withDefaults(defineProps<Props>(), {
    progress: undefined,
    existingLabels: () => []
})
</script>