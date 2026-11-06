<template>
  <div class="relative inline-block text-left" ref="toggleRef">
    <!-- Main Toggle Trigger Button -->
    <button 
      @click="toggleDropdown" 
      type="button"
      :title="currentLang === 'en' ? 'ቋንቋ ቀይር / Change Language' : 'Change Language / ቋንቋ ቀይር'"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      aria-label="Change Language / ቋንቋ ቀይር"
      :class="[
        'group relative flex items-center gap-2 h-9 px-3 rounded-xl border transition-all duration-200 cursor-pointer select-none text-xs font-semibold focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[#1E9444]',
        variant === 'farmer'
          ? 'bg-[#0F5C2A] hover:bg-[#157337] border-emerald-600/40 hover:border-emerald-400 text-white shadow-xs'
          : variant === 'glass'
            ? 'bg-slate-900/30 hover:bg-slate-900/50 border-white/20 hover:border-white/40 text-white backdrop-blur-md shadow-xs'
            : 'bg-[#F0F2F5] dark:bg-[#1E2328] hover:bg-[#E2E6EC] dark:hover:bg-[#2A313C] border-slate-300/80 dark:border-white/15 hover:border-[#1E9444] dark:hover:border-emerald-500 text-slate-800 dark:text-white shadow-2xs'
      ]"
    >
      <!-- Active Language Flag Avatar -->
      <span class="w-5 h-5 rounded-full overflow-hidden shrink-0 shadow-2xs ring-1 ring-black/10 dark:ring-white/20 flex items-center justify-center">
        <!-- English Flag -->
        <svg v-if="currentLang === 'en'" viewBox="0 0 32 32" class="w-full h-full object-cover">
          <rect width="32" height="32" fill="#012169" />
          <path d="M0 0 L32 32 M32 0 L0 32" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="square" />
          <path d="M0 0 L32 32 M32 0 L0 32" stroke="#C8102E" stroke-width="2.2" stroke-linecap="square" />
          <path d="M16 0 V32 M0 16 H32" stroke="#FFFFFF" stroke-width="6.5" />
          <path d="M16 0 V32 M0 16 H32" stroke="#C8102E" stroke-width="4" />
        </svg>

        <!-- Ethiopian Flag -->
        <svg v-else viewBox="0 0 32 32" class="w-full h-full object-cover">
          <rect width="32" height="10.67" y="0" fill="#009A44" />
          <rect width="32" height="10.67" y="10.67" fill="#FED100" />
          <rect width="32" height="10.67" y="21.33" fill="#EF3340" />
          <circle cx="16" cy="16" r="6.2" fill="#0F47AF" />
          <polygon points="16,11.2 17.5,14.8 21.2,14.8 18.2,17 19.3,20.6 16,18.3 12.7,20.6 13.8,17 10.8,14.8 14.5,14.8" fill="#FED100" />
          <circle cx="16" cy="16" r="1.3" fill="#0F47AF" />
          <circle cx="16" cy="16" r="0.7" fill="#FED100" />
        </svg>
      </span>

      <!-- Language Code Badge -->
      <span :class="[
        'text-[12px] font-black tracking-wider uppercase transition-colors',
        variant === 'farmer' || variant === 'glass'
          ? 'text-white'
          : 'text-slate-800 dark:text-white group-hover:text-slate-950 dark:group-hover:text-white'
      ]">
        {{ currentLang === 'en' ? 'EN' : 'አማ' }}
      </span>

      <!-- Animated Chevron Down -->
      <ChevronDown 
        :class="[
          'w-3.5 h-3.5 transition-all duration-200 shrink-0',
          isOpen ? 'rotate-180 text-[#1E9444]' : (
            variant === 'farmer' || variant === 'glass'
              ? 'text-white/80 group-hover:text-white'
              : 'text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white'
          )
        ]" 
      />
    </button>

    <!-- Professional Dropdown Popover -->
    <Transition name="dropdown-popover">
      <div 
        v-if="isOpen" 
        role="listbox"
        aria-label="Language selection"
        class="absolute right-0 top-full mt-2 w-60 bg-white/98 dark:bg-[#1E2328] backdrop-blur-xl border border-slate-200 dark:border-white/15 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-black/40 p-1.5 z-50 text-xs overflow-hidden"
      >
        <!-- Category Sub-header with Globe icon -->
        <div class="px-2.5 py-1.5 flex items-center justify-between border-b border-slate-100 dark:border-white/10 mb-1">
          <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">
            <Globe class="w-3.5 h-3.5 text-[#1E9444]" />
            <span>{{ currentLang === 'en' ? 'Select Language' : 'ቋንቋ ይምረጡ' }}</span>
          </div>
          <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">2</span>
        </div>

        <!-- English Option -->
        <button 
          type="button"
          role="option"
          :aria-selected="currentLang === 'en'"
          @click="selectLang('en')"
          :class="[
            'w-full flex items-center justify-between p-2 rounded-xl transition-all duration-150 cursor-pointer text-left group',
            currentLang === 'en' 
              ? 'bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 font-bold border border-emerald-500/20' 
              : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2A313C] border border-transparent font-medium'
          ]"
        >
          <div class="flex items-center gap-2.5">
            <!-- UK Flag Icon -->
            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 ring-1 ring-black/10 dark:ring-white/10 shadow-xs flex items-center justify-center">
              <svg viewBox="0 0 32 32" class="w-full h-full object-cover">
                <rect width="32" height="32" fill="#012169" />
                <path d="M0 0 L32 32 M32 0 L0 32" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="square" />
                <path d="M0 0 L32 32 M32 0 L0 32" stroke="#C8102E" stroke-width="2.2" stroke-linecap="square" />
                <path d="M16 0 V32 M0 16 H32" stroke="#FFFFFF" stroke-width="6.5" />
                <path d="M16 0 V32 M0 16 H32" stroke="#C8102E" stroke-width="4" />
              </svg>
            </div>
            <div class="flex flex-col text-left">
              <span class="text-xs font-bold leading-tight">English</span>
              <span :class="['text-[10px] leading-tight mt-0.5', currentLang === 'en' ? 'text-emerald-700/80 dark:text-emerald-400/80 font-medium' : 'text-slate-400 dark:text-slate-400']">
                Global / International
              </span>
            </div>
          </div>
          <div v-if="currentLang === 'en'" class="w-5 h-5 rounded-full bg-[#1E9444] text-white flex items-center justify-center shadow-xs shrink-0">
            <Check class="w-3 h-3 stroke-[3]" />
          </div>
        </button>

        <!-- Amharic Option -->
        <button 
          type="button"
          role="option"
          :aria-selected="currentLang === 'am'"
          @click="selectLang('am')"
          :class="[
            'w-full flex items-center justify-between p-2 rounded-xl transition-all duration-150 cursor-pointer text-left group mt-1',
            currentLang === 'am' 
              ? 'bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 font-bold border border-emerald-500/20' 
              : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2A313C] border border-transparent font-medium'
          ]"
        >
          <div class="flex items-center gap-2.5">
            <!-- Ethiopian Flag Icon -->
            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 ring-1 ring-black/10 dark:ring-white/10 shadow-xs flex items-center justify-center">
              <svg viewBox="0 0 32 32" class="w-full h-full object-cover">
                <rect width="32" height="10.67" y="0" fill="#009A44" />
                <rect width="32" height="10.67" y="10.67" fill="#FED100" />
                <rect width="32" height="10.67" y="21.33" fill="#EF3340" />
                <circle cx="16" cy="16" r="6.2" fill="#0F47AF" />
                <polygon points="16,11.2 17.5,14.8 21.2,14.8 18.2,17 19.3,20.6 16,18.3 12.7,20.6 13.8,17 10.8,14.8 14.5,14.8" fill="#FED100" />
                <circle cx="16" cy="16" r="1.3" fill="#0F47AF" />
                <circle cx="16" cy="16" r="0.7" fill="#FED100" />
              </svg>
            </div>
            <div class="flex flex-col text-left">
              <span class="text-xs font-bold leading-tight">አማርኛ</span>
              <span :class="['text-[10px] leading-tight mt-0.5', currentLang === 'am' ? 'text-emerald-700/80 dark:text-emerald-400/80 font-medium' : 'text-slate-400 dark:text-slate-400']">
                Amharic (Ethiopia)
              </span>
            </div>
          </div>
          <div v-if="currentLang === 'am'" class="w-5 h-5 rounded-full bg-[#1E9444] text-white flex items-center justify-center shadow-xs shrink-0">
            <Check class="w-3 h-3 stroke-[3]" />
          </div>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { ChevronDown, Check, Globe } from 'lucide-vue-next'
import { useLanguage } from '@/composables/useLanguage'

defineProps({
  variant: {
    type: String,
    default: 'default', // 'default' | 'farmer' | 'glass'
  }
})

const { currentLang, setLanguage } = useLanguage()
const isOpen = ref(false)
const toggleRef = ref(null)

function toggleDropdown() {
  isOpen.value = !isOpen.value
}

function selectLang(lang) {
  setLanguage(lang)
  isOpen.value = false
}

function handleClickOutside(e) {
  if (toggleRef.value && !toggleRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

function handleKeyDown(e) {
  if (e.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.dropdown-popover-enter-active,
.dropdown-popover-leave-active {
  transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
.dropdown-popover-enter-from,
.dropdown-popover-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}
</style>
