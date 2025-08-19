<template>
    <Index 
        :currentStep="4"
        :progress="progress"
        :existingBuildings="[]"
        :existingRooms="[]"
        :availableBuildings="[]"
        :existingGroups="[]"
        :existingUsers="existingUsers"
        :availableGroups="availableGroups"
        :existingLabels="[]"
    />
</template>

<script setup lang="ts">
import Index from './Index.vue'
import type { OnboardingProgress, OnboardingUser } from '@/types'

interface Props {
    progress?: OnboardingProgress
    existingUsers?: OnboardingUser[]
    availableGroups?: string[]
}

withDefaults(defineProps<Props>(), {
    progress: undefined,
    existingUsers: () => [],
    availableGroups: () => []
})
</script>