<template>
  <span :class="['inline-flex items-center font-semibold text-[11px] rounded-full px-2 py-0.5 bg-[#FFF8EC] dark:bg-amber-950/40 text-[#D88C0A] dark:text-amber-300 border border-[#FFE5A5] dark:border-amber-800/60', className]">
    {{ $t(grade) || grade }}
  </span>
</template>

<script setup>
defineProps({
  grade: { type: String, required: true },
  className: { type: String, default: '' },
})
</script>
