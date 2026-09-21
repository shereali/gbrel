import { ref } from 'vue'

export interface ToastMessage {
  id: number
  title: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration?: number
}

const toasts = ref<ToastMessage[]>([])

export const useToast = () => {
  const show = (title: string, message: string = '', type: 'success' | 'error' | 'warning' | 'info' = 'info', duration: number = 4000) => {
    const id = Date.now() + Math.random()
    const toast: ToastMessage = { id, title, message, type, duration }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        remove(id)
      }, duration)
    }

    return id
  }

  const success = (title: string, message: string = '', duration: number = 4000) => {
    return show(title, message, 'success', duration)
  }

  const error = (title: string, message: string = '', duration: number = 5000) => {
    return show(title, message, 'error', duration)
  }

  const warning = (title: string, message: string = '', duration: number = 4500) => {
    return show(title, message, 'warning', duration)
  }

  const info = (title: string, message: string = '', duration: number = 4000) => {
    return show(title, message, 'info', duration)
  }

  const remove = (id: number) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const add = (options: { title: string, message?: string, type?: 'success' | 'error' | 'warning' | 'info', duration?: number }) => {
    return show(options.title, options.message || '', options.type || 'info', options.duration || 4000)
  }

  return {
    toasts,
    show,
    add,
    success,
    error,
    warning,
    info,
    remove
  }
}
