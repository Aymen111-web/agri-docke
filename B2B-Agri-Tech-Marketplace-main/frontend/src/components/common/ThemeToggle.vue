<template>
  <button 
    @click="toggleTheme" 
    type="button"
    :title="isDark ? $t('common.switchToLight') : $t('common.switchToDark')"
    class="p-2.5 rounded-xl border border-[#E2E4E7] dark:border-white/20 bg-white dark:bg-white/10 text-[#1E2328] dark:text-white hover:bg-[#F0F1F2] dark:hover:bg-white/20 hover:border-[#1E9444] transition-all cursor-pointer flex items-center justify-center shadow-2xs group"
    :aria-label="$t('common.toggleThemeAria')"
  >
    <Sun v-if="isDark" class="w-4 h-4 text-amber-400 group-hover:rotate-45 transition-transform duration-300" />
    <Moon v-else class="w-4 h-4 text-[#5A6270] group-hover:-rotate-12 transition-transform duration-300" />
  </button>
</template>

<script setup>
import { Sun, Moon } from 'lucide-vue-next'
import { useTheme } from '@/composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>
