<template>
  <div class="space-y-6 lg:space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-[#E2E4E7] dark:border-[#30363D] pb-6 relative">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-1.5">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20">
            <ShieldAlert class="w-5 h-5 text-white" />
          </div>
          <h1 class="text-[28px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.disputeTitle') }}</h1>
        </div>
        <p class="text-[14px] font-medium text-[#5A6270] dark:text-[#8B949E] max-w-xl">
          {{ $t('admin.disputeSub') }}
        </p>
      </div>
      <div class="flex items-center gap-3 z-10">
        <div class="flex items-center bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-xl p-1 shadow-xs">
          <span class="px-3 py-1.5 text-[11px] font-black uppercase text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.activeClaims') }}</span>
          <span class="px-2.5 py-1 bg-[#F0F1F2] dark:bg-[#21262D] text-[#1E2328] dark:text-[#F0F6FC] rounded-lg text-xs font-bold">{{ disputes.length }}</span>
        </div>
        <button @click="loadDisputes" :disabled="isLoading" 
          class="px-4 py-2.5 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] hover:shadow-md transition-all active:scale-95 flex items-center gap-2 group cursor-pointer">
          <RefreshCcw :class="['w-4 h-4 text-[#5A6270] dark:text-[#8B949E] group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors', isLoading && 'animate-spin']" /> 
          {{ $t('admin.refresh') }}
        </button>
      </div>
      <div class="absolute right-0 top-0 w-64 h-32 bg-red-50 dark:bg-red-950/20 rounded-full blur-[80px] -z-0 opacity-60"></div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="w-16 h-16 rounded-2xl bg-white dark:bg-[#161B22] shadow-xl flex items-center justify-center mb-4 relative overflow-hidden border border-[#E2E4E7]/50 dark:border-[#30363D]">
        <div class="absolute inset-0 bg-gradient-to-tr from-rose-100 dark:from-rose-950/40 to-transparent opacity-50"></div>
        <Loader2 class="w-8 h-8 text-rose-500 animate-spin relative z-10" />
      </div>
      <p class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase tracking-wider mt-2">{{ $t('admin.syncingExceptions') }}</p>
      <p class="text-[11px] text-[#9BA1AA] dark:text-[#8B949E] font-bold mt-1">{{ $t('admin.retrievingAnomalyReports') }}</p>
    </div>

    <div v-else-if="disputes.length === 0" class="text-center py-20 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-[24px] px-8 shadow-sm relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white dark:from-[#21262D] dark:to-[#161B22] opacity-50 z-0 transition-opacity group-hover:opacity-100"></div>
      <div class="relative z-10">
        <div class="w-20 h-20 mx-auto bg-[#F8F9FA] dark:bg-[#21262D] rounded-full flex items-center justify-center shadow-inner mb-4 border border-[#E2E4E7] dark:border-[#30363D]">
          <CheckCircle2 class="w-10 h-10 text-emerald-500 opacity-80" />
        </div>
        <h3 class="text-[18px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.noActiveDisputes') }}</h3>
        <p class="text-[13px] font-medium text-[#5A6270] dark:text-[#8B949E] mt-1.5 max-w-sm mx-auto">
          {{ $t('admin.noActiveDisputesSub') }}
        </p>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5 relative z-10">
      <article v-for="d in disputes" :key="d.id" 
        class="bg-white dark:bg-[#161B22] border border-rose-200 dark:border-rose-900/50 rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all group flex flex-col justify-between">
        
        <div>
          <div @click="toggleTicket(d.id)" class="flex items-center justify-between border-b border-rose-100 dark:border-rose-900/40 pb-4 cursor-pointer select-none group/header">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-50 dark:from-rose-950/40 to-red-100 dark:to-red-950/40 text-rose-700 dark:text-rose-300 flex items-center justify-center font-bold shrink-0 border border-rose-200 dark:border-rose-800/40 shadow-2xs group-hover/header:rotate-6 transition-transform">
                <ShieldAlert class="w-5 h-5" />
              </div>
              <div class="min-w-0">
                <h3 class="text-[15px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] truncate">
                  {{ $t('admin.resolutionTicket', { id: d.id }) }}
                </h3>
                <p class="text-[11px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-400 mt-0.5 truncate">
                  {{ $t('admin.referenceOrder', { id: d.order_id }) }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="shrink-0 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 shadow-2xs border border-rose-200 dark:border-rose-800/50">
                {{ d.status === 'pending' || d.status === 'pending_review' ? $t('admin.needsAction') : $t(d.status) }}
              </span>
              <button class="p-1.5 rounded-lg bg-gray-100 dark:bg-[#21262D] group-hover/header:bg-gray-200 dark:group-hover/header:bg-[#30363D] text-gray-500 transition-colors cursor-pointer">
                <ChevronDown :class="['w-4 h-4 transition-transform duration-300', expandedTickets[d.id] ? 'rotate-180' : '']" />
              </button>
            </div>
          </div>

          <div v-if="expandedTickets[d.id]" class="animate-in slide-in-from-top-2 fade-in duration-300">
            <div class="py-4">
            <div class="grid grid-cols-2 gap-4 mb-4 text-[12px]">
              <div>
                <span class="block font-bold text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider text-[10px] mb-1">{{ $t('admin.claimType') }}</span>
                <span class="font-bold text-[#1E2328] dark:text-[#F0F6FC] capitalize">{{ d.type ? $t(d.type) : $t('admin.generalException') }}</span>
              </div>
              <div>
                <span class="block font-bold text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider text-[10px] mb-1">{{ $t('admin.filedBy') }}</span>
                <span class="font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ d.raised_user?.first_name ? (d.raised_user.first_name + ' ' + (d.raised_user.second_name || '')) : $t('admin.systemAutoFlag') }}</span>
              </div>
            </div>

            <div class="bg-rose-50/50 dark:bg-rose-950/20 p-4 rounded-xl border border-rose-100 dark:border-rose-900/40 relative overflow-hidden">
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-rose-400 to-red-500"></div>
              <span class="block font-bold text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider text-[9px] mb-1">{{ $t('admin.incidentDetailReport') }}</span>
              <p class="text-[13px] font-medium text-[#1E2328] dark:text-[#F0F6FC] leading-relaxed">
                {{ d.description || $t('admin.noDetailedDescription') }}
              </p>
            </div>

            <!-- Farmer Counter-Statement Box -->
            <div v-if="d.farmer_response" class="mt-3 bg-amber-50/60 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-200/60 dark:border-amber-900/40 relative overflow-hidden">
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-amber-400 to-amber-600"></div>
              <span class="block font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider text-[9px] mb-1">Farmer Counter-Statement & Defense</span>
              <p class="text-[13px] font-medium text-[#1E2328] dark:text-[#F0F6FC] leading-relaxed">
                {{ d.farmer_response }}
              </p>
            </div>
          </div>
        </div>
      </div>

        <!-- Finalized Verdict Banner OR Action Buttons -->
        <div v-if="d.status === 'resolved' || d.status === 'rejected'" class="mt-4 p-3.5 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 space-y-1">
          <div class="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-black text-[11px] uppercase tracking-wider">
            <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
            <span>Verdict Finalized & Escrow Decision Logged</span>
          </div>
          <p class="text-xs font-semibold text-emerald-950 dark:text-emerald-100 pl-5">
            {{ d.resolution_notes || 'Case finalized by Administrator.' }}
          </p>
        </div>

        <div v-else class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-gray-100 dark:border-[#30363D] mt-auto">
          <button @click="resolveDispute(d.id, 'refund_buyer')" 
            class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl border-2 border-orange-100 dark:border-orange-900/40 text-orange-700 dark:text-orange-300 text-[12px] font-bold hover:bg-orange-50 dark:hover:bg-orange-950/40 hover:border-orange-200 dark:hover:border-orange-800/50 transition-colors shadow-2xs focus:ring-4 focus:ring-orange-100 cursor-pointer">
            {{ $t('admin.refundBuyer') }}
          </button>
          <button @click="resolveDispute(d.id, 'release_farmer')" 
            class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-gradient-to-tr from-[#1E9444] to-emerald-500 text-white text-[12px] font-bold hover:from-[#0F5C2A] hover:to-emerald-600 flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/20 transition-all focus:ring-4 focus:ring-emerald-100 cursor-pointer">
            <Check class="w-4 h-4" /> {{ $t('admin.finalizePayoutFarmer') }}
          </button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ShieldAlert, Check, Loader2, RefreshCcw, CheckCircle2, ChevronDown } from 'lucide-vue-next'
import { adminApi } from '@/services/adminService'
import { useAlertModal } from '@/composables/useAlertModal'

const { showAlert } = useAlertModal()
const disputes = ref([])
const isLoading = ref(true)
const expandedTickets = ref({})

const toggleTicket = (id) => {
  expandedTickets.value[id] = !expandedTickets.value[id]
}

const loadDisputes = async () => {
  isLoading.value = true
  try {
    const res = await adminApi.fetchPaymentExceptions()
    disputes.value = res.data || res
  } catch (err) {
    disputes.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(loadDisputes)

const resolveDispute = async (id, resolution) => {
  const isRefund = resolution === 'refund_buyer'
  if (confirm(`CRITICAL ESCROW ARBITRAGE: Are you sure you want to ${isRefund ? 'REFUND THE BUYER' : 'PAY OUT THE FARMER'} for this transaction? This action will mutate escrow funds.`)) {
    try {
      const notes = prompt(`Please enter resolution/audit notes for ${isRefund ? 'refunding the buyer' : 'payout to farmer'}:`) || `Administratively resolved via ${resolution}`
      await adminApi.resolvePaymentException(id, resolution, notes)
      loadDisputes()
      showAlert({
        title: 'Escrow Action Complete',
        message: `Successfully ${isRefund ? 'refunded buyer' : 'released payout to farmer'} and logged audit record.`,
        type: 'success'
      })
    } catch (err) {
      showAlert({
        title: 'Escrow Action Error',
        message: err.message || 'Failed to resolve escalation',
        type: 'error'
      })
    }
  }
}
</script>
