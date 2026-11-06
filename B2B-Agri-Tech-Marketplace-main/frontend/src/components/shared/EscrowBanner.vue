<template>
  <div :class="['w-full bg-[#FFF8EC] dark:bg-amber-950/30 border border-[#FFE5A5] dark:border-amber-800/40 rounded-xl p-3.5 flex items-start gap-3 text-left', className]">
    <div class="w-8 h-8 rounded-full bg-[#F5B73A]/20 dark:bg-amber-500/20 flex items-center justify-center shrink-0 mt-0.5 text-[#D88C0A] dark:text-amber-400">
      <Lock class="w-4 h-4" />
    </div>
    <div>
      <h4 class="text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('marketplace.escrowProtection') }}</h4>
      <p class="text-[12px] text-[#5A6270] dark:text-[#8B949E] mt-0.5">
        {{ $t('marketplace.escrowProtectionDesc') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { Lock } from 'lucide-vue-next'

defineProps({
  className: { type: String, default: '' },
})
</script>
