<template>
  <span :class="['inline-flex items-center gap-1 font-semibold rounded-full px-2 py-0.5 bg-[#1E9444]/15 dark:bg-emerald-500/20 text-[#1E9444] dark:text-emerald-400', size === 'sm' ? 'text-[11px]' : 'text-[12px]', className]">
    <ShieldCheck :class="size === 'sm' ? 'w-3 h-3' : 'w-3.5 h-3.5'" />
    <span>{{ $t('common.verified') }}</span>
  </span>
</template>

<script setup>
import { ShieldCheck } from 'lucide-vue-next'

defineProps({
  size: { type: String, default: 'sm' },
  className: { type: String, default: '' },
})
</script>
