<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { SelectValue, type SelectValueProps } from 'reka-ui'

interface Props extends SelectValueProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <SelectValue
    data-slot="select-value"
    :class="cn('text-sm pointer-events-none', props.class)"
    v-bind="props"
  >
    <slot />
  </SelectValue>
</template>