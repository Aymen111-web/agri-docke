<template>
  <nav 
    v-if="totalItems > 0"
    aria-label="Pagination Navigation"
    class="flex items-center justify-between gap-4 py-4 px-1 border-t border-[#E2E4E7] dark:border-[#30363D] select-none text-xs"
  >
    <!-- Left: Arrow Controls with Only the Current Page Number -->
    <div class="flex items-center gap-1 sm:gap-1.5">
      <!-- First Page (<<) -->
      <button
        type="button"
        title="First Page"
        @click="goToPage(1)"
        :disabled="currentPage <= 1"
        :class="[
          'p-2 rounded-lg transition-colors flex items-center justify-center',
          currentPage <= 1
            ? 'opacity-30 cursor-not-allowed text-gray-400 dark:text-gray-600'
            : 'cursor-pointer text-[#0B57D0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 active:scale-95'
        ]"
      >
        <ChevronsLeft class="w-4 h-4" />
      </button>

      <!-- Previous Page (<) -->
      <button
        type="button"
        title="Previous Page"
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage <= 1"
        :class="[
          'p-2 rounded-lg transition-colors flex items-center justify-center',
          currentPage <= 1
            ? 'opacity-30 cursor-not-allowed text-gray-400 dark:text-gray-600'
            : 'cursor-pointer text-[#0B57D0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 active:scale-95'
        ]"
      >
        <ChevronLeft class="w-4 h-4" />
      </button>

      <!-- Only Current Page Number Displayed -->
      <div 
        class="w-8.5 h-8.5 rounded-xl bg-[#0B57D0] text-white font-black text-xs shadow-xs flex items-center justify-center"
      >
        {{ currentPage }}
      </div>

      <!-- Next Page (>) -->
      <button
        type="button"
        title="Next Page"
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage >= totalPages"
        :class="[
          'p-2 rounded-lg transition-colors flex items-center justify-center',
          currentPage >= totalPages
            ? 'opacity-30 cursor-not-allowed text-gray-400 dark:text-gray-600'
            : 'cursor-pointer text-[#0B57D0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 active:scale-95'
        ]"
      >
        <ChevronRight class="w-4 h-4" />
      </button>

      <!-- Last Page (>>) -->
      <button
        type="button"
        title="Last Page"
        @click="goToPage(totalPages)"
        :disabled="currentPage >= totalPages"
        :class="[
          'p-2 rounded-lg transition-colors flex items-center justify-center',
          currentPage >= totalPages
            ? 'opacity-30 cursor-not-allowed text-gray-400 dark:text-gray-600'
            : 'cursor-pointer text-[#0B57D0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 active:scale-95'
        ]"
      >
        <ChevronsRight class="w-4 h-4" />
      </button>

      <!-- Refresh (↻) -->
      <button
        type="button"
        title="Refresh"
        @click="handleRefresh"
        :class="[
          'p-2 rounded-lg transition-colors flex items-center justify-center cursor-pointer text-[#0B57D0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 active:scale-95',
          isSpinning && 'animate-spin'
        ]"
      >
        <RotateCw class="w-4 h-4" />
      </button>
    </div>

    <!-- Right: Count Range (e.g. 1 - 6 Of 24) -->
    <div class="text-[#0B57D0] dark:text-blue-400 font-bold text-xs sm:text-[13px] tracking-tight whitespace-nowrap">
      {{ startItem }} - {{ endItem }} {{ $t('common.of', 'Of') }} {{ totalItems }}
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, RotateCw } from 'lucide-vue-next'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
    default: 1
  },
  totalPages: {
    type: Number,
    required: true,
    default: 1
  },
  totalItems: {
    type: Number,
    required: true,
    default: 0
  },
  itemsPerPage: {
    type: Number,
    default: 6
  }
})

const emit = defineEmits(['update:currentPage', 'page-change', 'refresh'])

const isSpinning = ref(false)

const startItem = computed(() => {
  if (props.totalItems === 0) return 0
  return (props.currentPage - 1) * props.itemsPerPage + 1
})

const endItem = computed(() => {
  return Math.min(props.currentPage * props.itemsPerPage, props.totalItems)
})

const goToPage = (page) => {
  if (typeof page !== 'number') return
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('update:currentPage', page)
  emit('page-change', page)
}

const handleRefresh = () => {
  isSpinning.value = true
  emit('refresh')
  setTimeout(() => {
    isSpinning.value = false
  }, 600)
}
</script>
