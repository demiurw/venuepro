<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { SelectItem, type SelectItemProps } from 'reka-ui'

interface Props extends SelectItemProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <SelectItem
    data-slot="select-item"
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-md py-2 pl-2 pr-8 text-sm outline-none transition-smooth',
      'focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      'hover:bg-accent/50 hover:text-accent-foreground',
      props.class,
    )"
    v-bind="props"
  >
    <span class="absolute right-2 flex size-4 items-center justify-center">
      <SelectItemIndicator>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="size-4"
        >
          <path d="M20 6 9 17l-5-5" />
        </svg>
      </SelectItemIndicator>
    </span>
    <SelectItemText>
      <slot />
    </SelectItemText>
  </SelectItem>
</template>

<script lang="ts">
import { SelectItemIndicator, SelectItemText } from 'reka-ui'
</script>