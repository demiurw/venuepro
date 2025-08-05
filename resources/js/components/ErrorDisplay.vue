<template>
  <Alert :variant="alertVariant" v-if="shouldShow">
    <component :is="iconComponent" class="h-4 w-4" />
    <AlertTitle v-if="title">{{ title }}</AlertTitle>
    <AlertDescription>
      <slot>
        {{ message }}
      </slot>
    </AlertDescription>
    
    <!-- Action button slot -->
    <div v-if="$slots.action || actionText" class="mt-3">
      <slot name="action">
        <Button
          v-if="actionText"
          @click="$emit('action')"
          variant="outline"
          size="sm"
          :class="actionButtonClass"
        >
          {{ actionText }}
        </Button>
      </slot>
    </div>
  </Alert>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import {
  AlertCircle,
  CheckCircle,
  Info,
  AlertTriangle,
  Clock,
  Wifi,
  RefreshCw
} from 'lucide-vue-next'

export interface ErrorDisplayProps {
  type?: 'error' | 'success' | 'warning' | 'info' | 'network' | 'timeout'
  message?: string
  title?: string
  actionText?: string
  dismissible?: boolean
  autoHide?: boolean
  autoHideDelay?: number
}

const props = withDefaults(defineProps<ErrorDisplayProps>(), {
  type: 'error',
  dismissible: false,
  autoHide: false,
  autoHideDelay: 5000
})

const emit = defineEmits<{
  action: []
  dismiss: []
}>()

const alertVariant = computed(() => {
  const variants = {
    error: 'destructive',
    success: 'success',
    warning: 'warning',
    info: 'info',
    network: 'warning',
    timeout: 'warning'
  }
  return variants[props.type] || 'destructive'
})

const iconComponent = computed(() => {
  const icons = {
    error: AlertCircle,
    success: CheckCircle,
    warning: AlertTriangle,
    info: Info,
    network: Wifi,
    timeout: Clock
  }
  return icons[props.type] || AlertCircle
})

const actionButtonClass = computed(() => {
  const classes = {
    error: 'border-destructive/30 text-destructive hover:bg-destructive/10',
    success: 'border-green-500/30 text-green-600 hover:bg-green-50',
    warning: 'border-yellow-500/30 text-yellow-600 hover:bg-yellow-50',
    info: 'border-blue-500/30 text-blue-600 hover:bg-blue-50',
    network: 'border-yellow-500/30 text-yellow-600 hover:bg-yellow-50',
    timeout: 'border-yellow-500/30 text-yellow-600 hover:bg-yellow-50'
  }
  return classes[props.type] || classes.error
})

const shouldShow = computed(() => {
  return props.message || props.title || !!props.$slots.default
})

// Auto-hide functionality
if (props.autoHide && props.autoHideDelay > 0) {
  setTimeout(() => {
    emit('dismiss')
  }, props.autoHideDelay)
}
</script>