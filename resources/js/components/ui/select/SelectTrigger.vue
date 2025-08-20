<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { SelectTrigger, type SelectTriggerProps } from 'reka-ui'

interface Props extends SelectTriggerProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <SelectTrigger
    data-slot="select-trigger"
    :class="cn(
      'flex h-11 w-full items-center justify-between gap-2 rounded-lg border-2 border-input bg-background px-4 py-3 text-base shadow-soft transition-smooth placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus:border-primary focus:outline-none focus:ring-[3px] focus:ring-primary/15 focus:shadow-medium focus:-translate-y-0.5',
      'hover:border-border/60 hover:shadow-medium hover:-translate-y-0.5',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      'dark:bg-input/30 dark:border-input',
      '[&>span]:line-clamp-1',
      props.class,
    )"
    v-bind="props"
  >
    <slot />
    <SelectIcon as-child>
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
        class="size-4 opacity-50"
      >
        <path d="m7 15 5 5 5-5" />
        <path d="m7 9 5-5 5 5" />
      </svg>
    </SelectIcon>
  </SelectTrigger>
</template>

<script lang="ts">
import { SelectIcon } from 'reka-ui'
</script>