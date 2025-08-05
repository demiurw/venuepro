import { ref, readonly } from 'vue'

interface Toast {
  id: string
  title?: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration: number
  dismissible: boolean
}

interface ToastOptions {
  title?: string
  type?: Toast['type']
  duration?: number
  dismissible?: boolean
}

const toasts = ref<Toast[]>([])
let toastIdCounter = 0

export function useToast() {
  const show = (message: string, options: ToastOptions = {}) => {
    const toast: Toast = {
      id: `toast-${++toastIdCounter}`,
      message,
      title: options.title,
      type: options.type || 'info',
      duration: options.duration || 5000,
      dismissible: options.dismissible !== false
    }

    toasts.value.push(toast)

    // Auto-remove after duration
    if (toast.duration > 0) {
      setTimeout(() => {
        dismiss(toast.id)
      }, toast.duration)
    }

    return toast.id
  }

  const dismiss = (id: string) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const dismissAll = () => {
    toasts.value.splice(0)
  }

  // Convenience methods
  const success = (message: string, options: Omit<ToastOptions, 'type'> = {}) => {
    return show(message, { ...options, type: 'success' })
  }

  const error = (message: string, options: Omit<ToastOptions, 'type'> = {}) => {
    return show(message, { ...options, type: 'error' })
  }

  const warning = (message: string, options: Omit<ToastOptions, 'type'> = {}) => {
    return show(message, { ...options, type: 'warning' })
  }

  const info = (message: string, options: Omit<ToastOptions, 'type'> = {}) => {
    return show(message, { ...options, type: 'info' })
  }

  return {
    toasts: readonly(toasts),
    show,
    dismiss,
    dismissAll,
    success,
    error,
    warning,
    info
  }
}

// Create a global instance
export const toast = useToast()