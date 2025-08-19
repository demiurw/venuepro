<template>
    <Index 
        :currentStep="3"
        :progress="progress"
        :existingBuildings="[]"
        :existingRooms="[]"
        :availableBuildings="[]"
        :existingGroups="existingGroups"
        :existingUsers="[]"
        :availableGroups="[]"
        :existingLabels="[]"
    />
</template>

<script setup lang="ts">
import Index from './Index.vue'
import type { OnboardingProgress, OnboardingGroup } from '@/types'

interface Props {
    progress?: OnboardingProgress
    existingGroups?: OnboardingGroup[]
}

withDefaults(defineProps<Props>(), {
    progress: undefined,
    existingGroups: () => []
})
</script>