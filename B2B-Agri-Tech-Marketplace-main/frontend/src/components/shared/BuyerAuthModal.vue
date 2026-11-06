<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div 
          @click.stop 
          class="w-full max-w-md max-h-[85vh] overflow-y-auto bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-3xl p-6 shadow-2xl space-y-5 text-center relative animate-in fade-in zoom-in-95 duration-200"
        >
          <!-- Top Accent Line -->
          <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#062E15] via-[#1E9444] to-[#E69500]" />

          <!-- Close Icon -->
          <button 
            @click="close" 
            class="absolute top-4 right-4 p-1.5 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-[#F0F6FC] hover:bg-gray-100 dark:hover:bg-[#21262D] transition-colors cursor-pointer"
            aria-label="Close modal"
          >
            <X class="w-5 h-5" />
          </button>

          <!-- Header Icon -->
          <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/40 text-[#E69500] dark:text-amber-400 flex items-center justify-center mx-auto shadow-2xs">
            <ShoppingBag class="w-7 h-7 stroke-[2.2]" />
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <h3 class="text-xl font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('landing.readyToSource') }}</h3>
            <p class="text-xs text-[#5A6270] dark:text-[#8B949E] leading-relaxed max-w-xs mx-auto">
              {{ $t('landing.readyToSourceDesc') }}
            </p>
          </div>

          <!-- Benefits Pills -->
          <div class="grid grid-cols-2 gap-2 text-left bg-[#F8F9FA] dark:bg-[#21262D] p-3 rounded-2xl border border-gray-100 dark:border-[#30363D] text-[11px]">
            <div class="flex items-center gap-1.5 text-gray-700 dark:text-[#F0F6FC] font-medium">
              <ShieldCheck class="w-4 h-4 text-[#1E9444] shrink-0" />
              <span>{{ $t('landing.escrowPayment') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-gray-700 dark:text-[#F0F6FC] font-medium">
              <CheckCircle2 class="w-4 h-4 text-[#1E9444] shrink-0" />
              <span>{{ $t('landing.directFarmerTrade') }}</span>
            </div>
          </div>

          <!-- Buttons -->
          <div class="space-y-2.5 pt-1">
            <button 
              @click="handleRegister" 
              class="w-full py-3.5 px-4 rounded-xl bg-[#1E9444] hover:bg-[#0F5C2A] text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <UserPlus class="w-4 h-4" />
              <span>{{ $t('landing.registerCommercialBuyer') }}</span>
            </button>

            <button 
              @click="close" 
              class="w-full py-3 px-4 rounded-xl border border-gray-200 dark:border-[#30363D] bg-white dark:bg-[#21262D] hover:bg-gray-50 dark:hover:bg-[#30363D] text-[#5A6270] dark:text-[#F0F6FC] font-bold text-xs transition-colors cursor-pointer"
            >
              {{ $t('common.continue') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { X, ShoppingBag, ShieldCheck, CheckCircle2, UserPlus } from 'lucide-vue-next'

const props = defineProps({
  isOpen: { type: Boolean, default: false }
})

const emit = defineEmits(['close'])
const router = useRouter()

const close = () => {
  emit('close')
}

const handleRegister = () => {
  emit('close')
  router.push('/register?role=buyer')
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
