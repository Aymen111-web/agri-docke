import { ref } from 'vue'

const isOpen = ref(false)
const title = ref('Notification')
const message = ref('')
const type = ref('success') // 'success' | 'error' | 'warning' | 'info'
const confirmText = ref('OK')

export function useAlertModal() {
    const showAlert = ({ title: t = 'Notification', message: m = '', type: tp = 'success', confirmText: ct = 'OK' }) => {
        title.value = t
        message.value = m
        type.value = tp
        confirmText.value = ct
        isOpen.value = true
    }

    const closeAlert = () => {
        isOpen.value = false
    }

    return {
        isOpen,
        title,
        message,
        type,
        confirmText,
        showAlert,
        closeAlert
    }
}
