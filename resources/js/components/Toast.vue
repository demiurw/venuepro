<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
      <TransitionGroup
        name="toast"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="getToastClass(toast.type)"
          class="flex items-start gap-3 p-4 rounded-xl border shadow-lg bg-background max-w-sm"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mt-0.5">
            <component :is="getIcon(toast.type)" class="h-4 w-4" />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h4 v-if="toast.title" class="text-sm font-semibold mb-1">
              {{ toast.title }}
            </h4>
            <p class="text-sm leading-relaxed">
              {{ toast.message }}
            </p>
          </div>

          <!-- Dismiss Button -->
          <button
            v-if="toast.dismissible"
            @click="dismiss(toast.id)"
            class="flex-shrink-0 p-1 rounded-lg hover:bg-muted/50 transition-colors"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { toast } from '@/composables/useToast'
import {
  CheckCircle,
  AlertCircle,
  AlertTriangle,
  Info,
  X
} from 'lucide-vue-next'

const { toasts, dismiss } = toast

const getIcon = (type: string) => {
  const icons = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info
  }
  return icons[type as keyof typeof icons] || Info
}

const getToastClass = (type: string) => {
  const classes = {
    success: 'border-green-200 text-green-800 [&>div:first-child]:text-green-600',
    error: 'border-red-200 text-red-800 [&>div:first-child]:text-red-600',
    warning: 'border-yellow-200 text-yellow-800 [&>div:first-child]:text-yellow-600',
    info: 'border-blue-200 text-blue-800 [&>div:first-child]:text-blue-600'
  }
  return classes[type as keyof typeof classes] || classes.info
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>