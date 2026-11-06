<template>
  <div v-if="variant === 'icon'">
    <svg :width="size" :height="size" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" :class="className">
      <path d="M 40 160 A 84 84 0 1 1 182 85" stroke="#0B57D0" stroke-width="14" stroke-linecap="round" />
      <path d="M 175 105 A 72 72 0 1 0 52 162" stroke="#E69500" stroke-width="14" stroke-linecap="round" />
      <path d="M 125 130 C 135 142 165 178 190 184 C 172 192 148 186 132 168 Z" fill="#E69500" />
      <path d="M 64 140 V 70 L 100 115 L 136 70 V 140" stroke="#0B57D0" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" fill="none" />
      <path d="M 80 144 L 100 128 L 120 144" stroke="#E69500" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </svg>
  </div>

  <div v-else-if="variant === 'stacked'" :class="['flex flex-col items-center text-center', className]">
    <svg :width="size" :height="size" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M 40 160 A 84 84 0 1 1 182 85" stroke="#0B57D0" stroke-width="14" stroke-linecap="round" />
      <path d="M 175 105 A 72 72 0 1 0 52 162" stroke="#E69500" stroke-width="14" stroke-linecap="round" />
      <path d="M 125 130 C 135 142 165 178 190 184 C 172 192 148 186 132 168 Z" fill="#E69500" />
      <path d="M 64 140 V 70 L 100 115 L 136 70 V 140" stroke="#0B57D0" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" fill="none" />
      <path d="M 80 144 L 100 128 L 120 144" stroke="#E69500" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </svg>
    <div class="mt-2">
      <div class="flex items-center justify-center text-[22px] font-extrabold leading-tight">
        <span class="text-[#0B57D0] dark:text-blue-400">Agri</span>
        <span class="text-[#E69500] dark:text-amber-400">Gate</span>
      </div>
      <div v-if="showTagline" class="text-[10px] font-bold block mt-0.5 tracking-tight">
        <span class="text-[#5F6368] dark:text-gray-400">by </span>
        <span class="text-[#0B57D0] dark:text-blue-400">Qelem Meda </span>
        <span class="text-[#E69500] dark:text-amber-400">Technologies</span>
      </div>
    </div>
  </div>

  <div v-else :class="['flex items-center gap-3', className]">
    <svg :width="size" :height="size" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
      <path d="M 40 160 A 84 84 0 1 1 182 85" stroke="#0B57D0" stroke-width="14" stroke-linecap="round" />
      <path d="M 175 105 A 72 72 0 1 0 52 162" stroke="#E69500" stroke-width="14" stroke-linecap="round" />
      <path d="M 125 130 C 135 142 165 178 190 184 C 172 192 148 186 132 168 Z" fill="#E69500" />
      <path d="M 64 140 V 70 L 100 115 L 136 70 V 140" stroke="#0B57D0" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" fill="none" />
      <path d="M 80 144 L 100 128 L 120 144" stroke="#E69500" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </svg>
    <div class="flex flex-col justify-center">
      <div class="flex items-center text-[24px] font-black leading-none tracking-tight">
        <span class="text-[#0B57D0] dark:text-blue-400">Agri</span>
        <span class="text-[#E69500] dark:text-amber-400 ml-1">Gate</span>
      </div>
      <div v-if="showTagline" class="text-[11px] font-bold block mt-1 tracking-tight">
        <span class="text-[#5F6368] dark:text-gray-400">by </span>
        <span class="text-[#0B57D0] dark:text-blue-400">Qelem Meda </span>
        <span class="text-[#E69500] dark:text-amber-400">Technologies</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  size: { type: Number, default: 48 },
  variant: { type: String, default: 'full' },
  className: { type: String, default: '' },
  showTagline: { type: Boolean, default: true },
})
</script>
