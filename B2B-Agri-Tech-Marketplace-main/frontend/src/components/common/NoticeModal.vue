<template>
  <Transition name="modal">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs select-none"
      @click.self="closeAlert"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="w-full max-w-[380px] max-h-[85vh] overflow-y-auto bg-white dark:bg-[#161B22] rounded-[22px] p-6 shadow-2xl border border-slate-100 dark:border-[#30363D] relative transform transition-all"
      >
        <!-- Top subtle accent bar -->
        <div 
          :class="[
            'absolute top-0 left-0 right-0 h-[3px]',
            type === 'error' ? 'bg-gradient-to-r from-red-500 via-rose-300 to-transparent' :
            type === 'warning' ? 'bg-gradient-to-r from-amber-500 via-yellow-300 to-transparent' :
            type === 'info' ? 'bg-gradient-to-r from-blue-500 via-sky-300 to-transparent' :
            'bg-gradient-to-r from-[#1E9444] via-emerald-300 to-transparent'
          ]"
        />

        <!-- Top Content Row: Icon Badge + Text -->
        <div class="flex items-start gap-3.5">
          <!-- Circular Badge with Icon -->
          <div 
            :class="[
              'w-12 h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs',
              type === 'error' ? 'bg-red-50 dark:bg-rose-950/60 text-red-500' :
              type === 'warning' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-500' :
              type === 'info' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-500' :
              'bg-[#EDFAF2] dark:bg-emerald-950/60 text-[#1E9444] dark:text-emerald-400'
            ]"
          >
            <CheckCircle2 v-if="type === 'success'" class="w-6 h-6" />
            <AlertTriangle v-else-if="type === 'warning'" class="w-6 h-6" />
            <XCircle v-else-if="type === 'error'" class="w-6 h-6" />
            <Info v-else class="w-6 h-6" />
          </div>

          <!-- Title and Description -->
          <div class="flex-1 pt-0.5 min-w-0">
            <h3 class="text-[17px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight leading-tight">
              {{ title }}
            </h3>
            <p class="text-[13px] text-slate-500 dark:text-[#8B949E] font-medium leading-snug mt-1.5 whitespace-pre-line break-words">
              {{ message }}
            </p>
          </div>
        </div>

        <!-- Action Button Row -->
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            @click="closeAlert"
            :class="[
              'w-full py-2.5 px-4 rounded-xl font-bold text-[14px] transition-all shadow-xs cursor-pointer text-center',
              type === 'error' ? 'bg-red-600 hover:bg-red-700 text-white' :
              type === 'warning' ? 'bg-amber-600 hover:bg-amber-700 text-white' :
              type === 'info' ? 'bg-[#0B57D0] hover:bg-blue-700 text-white' :
              'bg-[#1E9444] hover:bg-[#0F5C2A] text-white'
            ]"
          >
            {{ confirmText || 'OK' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { CheckCircle2, AlertTriangle, XCircle, Info } from 'lucide-vue-next'
import { useAlertModal } from '@/composables/useAlertModal'

const { isOpen, title, message, type, confirmText, closeAlert } = useAlertModal()

function handleKeyDown(e) {
  if (e.key === 'Escape' && isOpen.value) {
    closeAlert()
  }
}

onMounted(() => document.addEventListener('keydown', handleKeyDown))
onUnmounted(() => document.removeEventListener('keydown', handleKeyDown))
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>
