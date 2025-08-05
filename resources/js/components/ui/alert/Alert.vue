<template>
  <div :class="alertVariants({ variant })" v-bind="$attrs">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import { cva, type VariantProps } from 'class-variance-authority'

const alertVariants = cva(
  'relative w-full rounded-xl border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
  {
    variants: {
      variant: {
        default: 'bg-background text-foreground border-border',
        destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive bg-destructive/10',
        success: 'border-green-500/50 text-green-800 dark:text-green-300 [&>svg]:text-green-600 bg-green-50 dark:bg-green-900/20',
        warning: 'border-yellow-500/50 text-yellow-800 dark:text-yellow-300 [&>svg]:text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20',
        info: 'border-blue-500/50 text-blue-800 dark:text-blue-300 [&>svg]:text-blue-600 bg-blue-50 dark:bg-blue-900/20',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

export interface AlertProps extends /* @vue-ignore */ VariantProps<typeof alertVariants> {}

defineProps<AlertProps>()
</script>