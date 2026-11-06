<template>
  <Transition name="modal">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs select-none"
      @click.self="emit('close')"
      role="dialog"
      aria-modal="true"
      aria-labelledby="delete-modal-title"
    >
      <div
        class="w-full max-w-[370px] max-h-[85vh] overflow-y-auto bg-white dark:bg-[#161B22] rounded-[22px] p-6 shadow-2xl border border-slate-100 dark:border-[#30363D] relative"
      >
        <!-- Top subtle accent bar -->
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#EE5D50] via-rose-300 to-transparent" />

        <!-- Top Content Row: Icon Badge + Text -->
        <div class="flex items-start gap-3.5">
          <!-- Circular Pink/Red Badge with Trash Icon -->
          <div class="w-12 h-12 rounded-full bg-[#FEECEB] dark:bg-rose-950/60 flex items-center justify-center shrink-0">
            <Trash2 class="w-5 h-5 text-[#EE5D50]" />
          </div>

          <!-- Title and Description -->
          <div class="flex-1 pt-0.5">
            <h3 id="delete-modal-title" class="text-[17px] font-black text-[#0B4A8F] dark:text-blue-400 tracking-tight leading-tight">
              {{ titleText }}
            </h3>
            <p class="text-[13px] text-slate-500 dark:text-[#8B949E] font-medium leading-snug mt-1">
              {{ descriptionText }}
            </p>
          </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="flex items-center gap-3 mt-6">
          <button
            type="button"
            @click="emit('close')"
            :disabled="isDeleting"
            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#21262D] hover:bg-slate-50 dark:hover:bg-[#30363D] text-slate-700 dark:text-[#F0F6FC] font-bold text-[14px] transition-all cursor-pointer text-center shadow-2xs disabled:opacity-60"
          >
            {{ noText }}
          </button>

          <button
            type="button"
            @click="emit('confirm')"
            :disabled="isDeleting"
            class="flex-1 py-2.5 px-4 rounded-xl bg-[#EE5D50] hover:bg-[#E0483A] text-white font-bold text-[14px] transition-all shadow-sm hover:shadow-md cursor-pointer text-center flex items-center justify-center gap-2 disabled:opacity-60"
          >
            <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
            <span>{{ isDeleting ? deletingText : yesText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { Trash2, Loader2 } from 'lucide-vue-next'
import { useLanguage } from '@/composables/useLanguage'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  listingTitle: {
    type: String,
    default: ''
  },
  isDeleting: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'confirm'])
const { t } = useLanguage()

const titleText = computed(() => {
  const res = t('farmer.deleteListingTitle', 'Delete Listing')
  return (!res || res.startsWith('farmer.')) ? 'Delete Listing' : res
})

const descriptionText = computed(() => {
  const res = t('farmer.deleteListingConfirm', 'Are you sure you want to delete this item?')
  return (!res || res.startsWith('farmer.')) ? 'Are you sure you want to delete this item?' : res
})

const noText = computed(() => {
  const res = t('common.no', 'No')
  return (!res || res.startsWith('common.')) ? 'No' : res
})

const yesText = computed(() => {
  const res = t('common.yes', 'Yes')
  return (!res || res.startsWith('common.')) ? 'Yes' : res
})

const deletingText = computed(() => {
  const res = t('common.deleting', 'Deleting...')
  return (!res || res.startsWith('common.')) ? 'Deleting...' : res
})

function handleKeyDown(e) {
  if (e.key === 'Escape' && props.isOpen && !props.isDeleting) {
    emit('close')
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
