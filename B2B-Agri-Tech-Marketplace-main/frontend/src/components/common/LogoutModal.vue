<template>
  <Transition name="modal">
    <div
      v-if="isLogoutModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs select-none"
      @click.self="closeLogoutModal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="logout-title"
    >
      <div
        class="w-full max-w-[370px] max-h-[85vh] overflow-y-auto bg-white dark:bg-[#161B22] rounded-[22px] p-6 shadow-2xl border border-slate-100 dark:border-[#30363D] relative"
      >
        <!-- Top subtle accent bar -->
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#EE5D50] via-rose-300 to-transparent" />

        <!-- Top Content Row: Icon Badge + Text -->
        <div class="flex items-start gap-3.5">
          <!-- Circular Pink/Red Badge with Logout Icon -->
          <div class="w-12 h-12 rounded-full bg-[#FEECEB] dark:bg-rose-950/60 flex items-center justify-center shrink-0">
            <LogOut class="w-5 h-5 text-[#EE5D50]" />
          </div>

          <!-- Title and Description -->
          <div class="flex-1 pt-0.5">
            <h3 id="logout-title" class="text-[17px] font-black text-[#0B4A8F] dark:text-blue-400 tracking-tight leading-tight">
              {{ t('common.signOut') || t('signOut') || 'Sign Out' }}
            </h3>
            <p class="text-[13px] text-slate-500 dark:text-[#8B949E] font-medium leading-snug mt-1">
              {{ t('common.signOutConfirm') || t('signOutConfirm') || 'Are you sure you want to sign out?' }}
            </p>
          </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="flex items-center gap-3 mt-6">
          <button
            type="button"
            @click="closeLogoutModal"
            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#21262D] hover:bg-slate-50 dark:hover:bg-[#30363D] text-slate-700 dark:text-[#F0F6FC] font-bold text-[14px] transition-all cursor-pointer text-center shadow-2xs"
          >
            {{ t('common.cancel') || t('cancel') || 'Cancel' }}
          </button>

          <button
            type="button"
            @click="handleConfirmLogout"
            :disabled="isSubmitting"
            class="flex-1 py-2.5 px-4 rounded-xl bg-[#EE5D50] hover:bg-[#E0483A] text-white font-bold text-[14px] transition-all shadow-sm hover:shadow-md cursor-pointer text-center flex items-center justify-center gap-2 disabled:opacity-60"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
            <span>{{ t('common.signOut') || t('signOut') || 'Sign Out' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut, Loader2 } from 'lucide-vue-next'
import { useAuth } from '@/composables/useAuth'
import { useLanguage } from '@/composables/useLanguage'

const router = useRouter()
const { isLogoutModalOpen, closeLogoutModal, logout } = useAuth()
const { t } = useLanguage()
const isSubmitting = ref(false)

async function handleConfirmLogout() {
  isSubmitting.value = true
  try {
    await logout()
  } finally {
    isSubmitting.value = false
    closeLogoutModal()
    router.push('/login')
  }
}

function handleKeyDown(e) {
  if (e.key === 'Escape' && isLogoutModalOpen.value) {
    closeLogoutModal()
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
