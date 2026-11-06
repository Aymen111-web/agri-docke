<template>
  <div
    v-if="showPrompt"
    class="fixed bottom-4 left-1/2 -translate-x-1/2 w-[90%] max-w-sm bg-white rounded-xl shadow-2xl p-4 flex flex-col sm:flex-row items-center gap-4 z-50 border border-gray-100"
  >
    <div class="flex-shrink-0">
      <img src="/qmt.webp" alt="AgriGate Logo" class="w-12 h-12 rounded-lg object-contain bg-gray-50 border border-gray-100 p-1" />
    </div>
    <div class="flex-grow text-center sm:text-left">
      <h3 class="text-[#1E2328] font-bold text-sm">Download AgriGate</h3>
      <p class="text-xs text-gray-500 mt-1">Get the app for a faster experience</p>
    </div>
    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
      <button
        @click="dismissPrompt"
        class="flex-1 sm:flex-none px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
      >
        Later
      </button>
      <button
        @click="installApp"
        class="flex-1 sm:flex-none px-3 py-2 text-xs font-semibold text-white bg-[#1E9444] hover:bg-[#167d36] rounded-lg transition-colors shadow-sm"
      >
        Install
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const deferredPrompt = ref(null)
const showPrompt = ref(false)

const handleBeforeInstallPrompt = (e) => {
  // Prevent the mini-infobar from appearing on mobile
  e.preventDefault()
  
  // Stash the event so it can be triggered later.
  deferredPrompt.value = e
  
  // Check if we previously dismissed it (optional)
  const isDismissed = localStorage.getItem('agrigate-pwa-dismissed')
  
  if (!isDismissed) {
    showPrompt.value = true
  }
}

const installApp = async () => {
  if (!deferredPrompt.value) return
  
  // Show the install prompt
  deferredPrompt.value.prompt()
  
  // Wait for the user to respond to the prompt
  const { outcome } = await deferredPrompt.value.userChoice
  
  console.log(`User response to the install prompt: ${outcome}`)
  
  // We've used the prompt, and can't use it again, throw it away
  deferredPrompt.value = null
  showPrompt.value = false
}

const dismissPrompt = () => {
  showPrompt.value = false
  // Optionally store in localStorage so we don't nag them again soon
  localStorage.setItem('agrigate-pwa-dismissed', 'true')
}

onMounted(() => {
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  
  // If the app is successfully installed
  window.addEventListener('appinstalled', () => {
    // Clear the deferredPrompt so it can be garbage collected
    deferredPrompt.value = null
    showPrompt.value = false
    console.log('AgriGate was successfully installed')
  })
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
})
</script>
