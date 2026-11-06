<template>
  <div :class="['w-full bg-[#F8F9FA] dark:bg-[#161B22] rounded-xl p-3 border border-[#E2E4E7] dark:border-[#30363D]', className]">
    <div class="flex items-center justify-between pb-2 border-b border-[#E2E4E7] dark:border-[#30363D] mb-2.5">
      <div class="flex items-center gap-1.5">
        <ShieldCheck class="w-3.5 h-3.5 text-[#1E9444] dark:text-emerald-400" />
        <span class="text-[11px] font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('orders.timeline') }}</span>
      </div>
      <span class="text-[10px] font-semibold text-[#5A6270] dark:text-[#8B949E]">
        {{ $t('auth.stepOf') }} {{ isCompletedState ? steps.length : activeStepIndex + 1 }} / {{ steps.length }}
      </span>
    </div>

    <!-- Responsive Horizontal Stepper for sm+ -->
    <div class="hidden sm:grid sm:grid-cols-5 gap-2 relative pt-1">
      <div v-for="(step, idx) in steps" :key="step.key" class="flex flex-col items-center text-center relative group">
        <!-- Connecting Line between steps -->
        <div v-if="idx < steps.length - 1" 
          :class="['absolute top-3 left-1/2 w-full h-0.5 -z-0 transition-colors', isDone(idx + 1) ? 'bg-[#1E9444]' : 'bg-[#E2E4E7] dark:bg-[#30363D]']" />

        <!-- Step Indicator -->
        <div :class="[
          'relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border transition-all shrink-0 mb-1.5',
          isDone(idx) ? 'bg-[#1E9444] border-[#1E9444] text-white shadow-xs' :
          isActive(idx) ? 'bg-white dark:bg-[#161B22] border-[#1E9444] text-[#1E9444] dark:text-emerald-400 ring-2 ring-emerald-100 dark:ring-emerald-950/50' :
          'bg-white dark:bg-[#21262D] border-[#E2E4E7] dark:border-[#30363D] text-gray-400 dark:text-gray-500'
        ]">
          <Check v-if="isDone(idx)" class="w-3 h-3 stroke-[3]" />
          <span v-else-if="isActive(idx)" class="w-1.5 h-1.5 rounded-full bg-[#1E9444] animate-pulse" />
          <span v-else>{{ idx + 1 }}</span>
        </div>

        <!-- Step Title -->
        <span :class="['text-[10px] font-bold leading-tight max-w-[120px]', (isDone(idx) || isActive(idx)) ? 'text-[#1E2328] dark:text-[#F0F6FC]' : 'text-gray-400 dark:text-gray-500']">
          {{ step.title }}
        </span>
        <span v-if="isActive(idx)" class="mt-0.5 px-1.5 py-0.2 rounded-full text-[8px] font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
          {{ $t('timelineSteps.active') }}
        </span>
      </div>
    </div>

    <!-- Mobile Compact Vertical Timeline -->
    <div class="sm:hidden relative pl-5 space-y-2">
      <div class="absolute top-1.5 bottom-1.5 left-2 w-0.5 bg-[#E2E4E7] dark:bg-[#30363D] -z-0" />
      <div v-for="(step, idx) in steps" :key="step.key" class="relative z-10 flex items-center justify-between gap-2">
        <div :class="[
          'absolute -left-5 w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-bold border transition-all',
          isDone(idx) ? 'bg-[#1E9444] border-[#1E9444] text-white' :
          isActive(idx) ? 'bg-white dark:bg-[#161B22] border-[#1E9444] text-[#1E9444]' :
          'bg-white dark:bg-[#21262D] border-[#E2E4E7] dark:border-[#30363D] text-gray-400'
        ]">
          <Check v-if="isDone(idx)" class="w-2.5 h-2.5 stroke-[3]" />
          <span v-else-if="isActive(idx)" class="w-1.5 h-1.5 rounded-full bg-[#1E9444] animate-pulse" />
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <span :class="['text-[11px] font-bold', (isDone(idx) || isActive(idx)) ? 'text-[#1E2328] dark:text-[#F0F6FC]' : 'text-gray-400']">
          {{ step.title }}
        </span>
        <span v-if="isDone(idx)" class="text-[9px] font-bold text-[#1E9444] bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">{{ $t('timelineSteps.done') }}</span>
        <span v-else-if="isActive(idx)" class="text-[9px] font-bold text-amber-700 bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded">{{ $t('timelineSteps.active') }}</span>
        <span v-else class="text-[9px] text-gray-400">{{ $t('timelineSteps.pending') }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Check, ShieldCheck } from 'lucide-vue-next'
import { useLanguage } from '@/composables/useLanguage'

const props = defineProps({
  status: { type: String, required: true },
  className: { type: String, default: '' },
})

const { t } = useLanguage()

const steps = computed(() => [
  { 
    key: 'placed', 
    title: t('timelineSteps.placedTitle'), 
    description: t('timelineSteps.placedDesc') 
  },
  { 
    key: 'accepted', 
    title: t('timelineSteps.acceptedTitle'), 
    description: t('timelineSteps.acceptedDesc') 
  },
  { 
    key: 'paid_in_escrow', 
    title: t('timelineSteps.paidEscrowTitle'), 
    description: t('timelineSteps.paidEscrowDesc') 
  },
  { 
    key: 'dispatched', 
    title: t('timelineSteps.dispatchedTitle'), 
    description: t('timelineSteps.dispatchedDesc') 
  },
  { 
    key: 'delivered', 
    title: t('timelineSteps.deliveredTitle'), 
    description: t('timelineSteps.deliveredDesc') 
  },
])

const statusStepMap = {
  placed: 0,
  pending: 0,
  
  accepted: 1,
  pending_payment: 1,
  pending_farmer_approval: 1,
  awaiting_buyer_payment: 1,

  paid_in_escrow: 2,
  confirmed: 2,

  dispatched: 3,
  
  in_transit: 4,
  buyer_received: 4,
  
  delivered: 4,
  completed: 4,
  disputed: 2,
}

const activeStepIndex = computed(() => statusStepMap[props.status] ?? 0)

const isCompletedState = computed(() => props.status === 'completed' || props.status === 'delivered')

const isDone = (idx) => isCompletedState.value ? idx <= activeStepIndex.value : idx < activeStepIndex.value
const isActive = (idx) => isCompletedState.value ? false : idx === activeStepIndex.value
</script>
