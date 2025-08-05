<template>
  <Badge
    :variant="badgeVariant"
    :size="size"
    :class="cn('inline-flex items-center gap-1.5', $attrs.class)"
  >
    <div 
      :class="statusDotClass" 
      class="w-1.5 h-1.5 rounded-full"
    />
    {{ statusLabel }}
  </Badge>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'
import type { VariantProps } from 'class-variance-authority'

export interface StatusBadgeProps {
  status: 'active' | 'inactive' | 'pending'
  size?: VariantProps<typeof Badge>['size']
  showIcon?: boolean
  customLabel?: string
  class?: string
}

const props = withDefaults(defineProps<StatusBadgeProps>(), {
  size: 'default',
  showIcon: true
})

const statusConfig = {
  active: {
    variant: 'active' as const,
    label: 'Active',
    dotClass: 'bg-green-500',
    description: 'User can access the system normally'
  },
  inactive: {
    variant: 'inactive' as const,
    label: 'Inactive',
    dotClass: 'bg-gray-400',
    description: 'User account is disabled'
  },
  pending: {
    variant: 'pending' as const,
    label: 'Pending Verification',
    dotClass: 'bg-orange-500',
    description: 'User needs to verify their account'
  }
}

const badgeVariant = computed(() => statusConfig[props.status].variant)
const statusLabel = computed(() => props.customLabel || statusConfig[props.status].label)
const statusDotClass = computed(() => statusConfig[props.status].dotClass)

// Expose status description for tooltips
defineExpose({
  description: computed(() => statusConfig[props.status].description)
})
</script>