import { createApp, h, ref } from 'vue'
import Toast from '@/components/Toast.vue'

const toasts = ref([])

const createToast = (message, type = 'info', duration = 3000) => {
  const container = document.createElement('div')
  container.className = 'toast-wrapper'
  document.body.appendChild(container)
  
  const app = createApp({
    setup() {
      const visible = ref(true)
      
      const handleClose = () => {
        visible.value = false
        setTimeout(() => {
          app.unmount()
          container.remove()
        }, 300)
      }
      
      // 自动关闭
      if (duration > 0) {
        setTimeout(handleClose, duration)
      }
      
      return () => h(Toast, {
        message,
        type,
        duration,
        modelValue: visible.value,
        'onUpdate:modelValue': handleClose
      })
    }
  })
  
  app.mount(container)
}

export const toast = {
  success(message, duration = 3000) {
    createToast(message, 'success', duration)
  },
  error(message, duration = 3000) {
    createToast(message, 'error', duration)
  },
  warning(message, duration = 3000) {
    createToast(message, 'warning', duration)
  },
  info(message, duration = 3000) {
    createToast(message, 'info', duration)
  }
}

export default toast
