<template>
  <div :class="badgeVariants({ variant: props.variant, size: props.size, class: props.class })">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { cva, type VariantProps } from 'class-variance-authority'

const badgeVariants = cva(
  'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        default: 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
        secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        destructive: 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
        outline: 'text-foreground',
        success: 'border-transparent bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
        warning: 'border-transparent bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
        pending: 'border-transparent bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300',
        inactive: 'border-transparent bg-gray-100 text-gray-600 dark:bg-gray-900/20 dark:text-gray-400',
        active: 'border-transparent bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
      },
      size: {
        default: 'px-2.5 py-0.5 text-xs',
        sm: 'px-2 py-0.5 text-xs',
        lg: 'px-3 py-1 text-sm',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)

export interface BadgeProps extends /* @vue-ignore */ VariantProps<typeof badgeVariants> {
  class?: any
}

const props = withDefaults(defineProps<BadgeProps>(), {
  variant: 'default',
  size: 'default',
})
</script>