<template>
  <div class="space-y-6 lg:space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-[#E2E4E7] dark:border-[#30363D] pb-6 relative">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-1.5">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <ShoppingBag class="w-5 h-5 text-white" />
          </div>
          <h1 class="text-[28px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.orderAuditTitle') }}</h1>
        </div>
        <p class="text-[14px] font-medium text-[#5A6270] dark:text-[#8B949E] max-w-xl">
          {{ $t('admin.orderAuditSub') }}
        </p>
      </div>
      <div class="flex items-center gap-3 z-10">
        <div class="flex items-center bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-xl p-1 shadow-xs">
          <span class="px-3 py-1.5 text-[11px] font-black uppercase text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.totalOrdersCount') }}</span>
          <span class="px-2.5 py-1 bg-[#F0F1F2] dark:bg-[#21262D] text-[#1E2328] dark:text-[#F0F6FC] rounded-lg text-xs font-bold">{{ orders.length }}</span>
        </div>
        <button @click="loadOrders" :disabled="isLoading" 
          class="px-4 py-2.5 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] hover:shadow-md transition-all active:scale-95 flex items-center gap-2 group cursor-pointer">
          <RefreshCcw :class="['w-4 h-4 text-[#5A6270] dark:text-[#8B949E] group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors', isLoading && 'animate-spin']" /> 
          {{ $t('admin.refresh') }}
        </button>
      </div>
      <div class="absolute right-0 top-0 w-64 h-32 bg-blue-50 dark:bg-blue-950/20 rounded-full blur-[80px] -z-0 opacity-60"></div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="w-16 h-16 rounded-2xl bg-white dark:bg-[#161B22] shadow-xl flex items-center justify-center mb-4 relative overflow-hidden border border-[#E2E4E7]/50 dark:border-[#30363D]">
        <div class="absolute inset-0 bg-gradient-to-tr from-blue-100 dark:from-blue-950/40 to-transparent opacity-50"></div>
        <Loader2 class="w-8 h-8 text-blue-500 animate-spin relative z-10" />
      </div>
      <p class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase tracking-wider mt-2">{{ $t('admin.syncingTransactions') }}</p>
      <p class="text-[11px] text-[#9BA1AA] dark:text-[#8B949E] font-bold mt-1">{{ $t('admin.fetchingLatestOrders') }}</p>
    </div>

    <div v-else-if="orders.length === 0" class="text-center py-20 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-[24px] px-8 shadow-sm relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white dark:from-[#21262D] dark:to-[#161B22] opacity-50 z-0 transition-opacity group-hover:opacity-100"></div>
      <div class="relative z-10">
        <div class="w-20 h-20 mx-auto bg-[#F8F9FA] dark:bg-[#21262D] rounded-full flex items-center justify-center shadow-inner mb-4 border border-[#E2E4E7] dark:border-[#30363D]">
          <ShoppingBag class="w-10 h-10 text-[#5A6270] dark:text-[#8B949E] opacity-40" />
        </div>
        <h3 class="text-[18px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.noOrdersFoundTitle') }}</h3>
        <p class="text-[13px] font-medium text-[#5A6270] dark:text-[#8B949E] mt-1.5 max-w-sm mx-auto">
          {{ $t('admin.noOrdersFoundSub') }}
        </p>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div class="space-y-2.5 relative z-10">
        <article v-for="(order, index) in paginatedOrders" :key="order.id" 
          class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-xl px-4 py-3 shadow-2xs hover:border-[#0B57D0]/50 dark:hover:border-blue-500/50 transition-all group">
          
          <!-- Main Compact Horizontal Row -->
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <!-- Left: Icon + Batch / Parties -->
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-sky-100 dark:from-blue-950/50 dark:to-sky-950/40 border border-blue-200 dark:border-blue-800/50 flex items-center justify-center shrink-0 shadow-2xs text-blue-700 dark:text-blue-300">
                <ShoppingBag class="w-4 h-4" />
              </div>
              
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-sm font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight truncate flex items-center gap-1.5">
                    <span class="text-gray-400 font-bold">#{{ (currentPage - 1) * itemsPerPage + index + 1 }}</span>
                    <span>{{ order.order_number || ('ORD-' + String(order.id).slice(-6)) }}</span>
                  </h3>
                  <span :class="['px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider', 
                    order.status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50' : 
                    order.status === 'disputed' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50' : 
                    'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50']">
                    {{ $t(order.status || 'placed') }}
                  </span>
                  <span class="text-[11px] text-[#5A6270] dark:text-[#8B949E] font-medium flex items-center gap-1">
                    <Calendar class="w-3 h-3 opacity-60" /> {{ formatDate(order.placed_at || order.created_at || new Date()) }}
                  </span>
                </div>
                
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-0.5 text-[11px] font-medium text-[#5A6270] dark:text-[#8B949E]">
                  <span>{{ $t('admin.buyerCustomer') }}: <strong class="text-[#1E2328] dark:text-[#F0F6FC]">{{ order.buyer?.name || order.buyer?.first_name || $t('common.anonymous') }}</strong></span>
                  <span class="text-gray-300 dark:text-gray-600">•</span>
                  <span>{{ $t('admin.producerSupplier') }}: <strong class="text-[#1E2328] dark:text-[#F0F6FC]">{{ order.farmer?.name || order.farmer?.first_name || $t('common.anonymous') }}</strong></span>
                  <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>
                  <span class="hidden sm:inline">{{ order.payment_method || 'Chapa Escrow' }} ({{ order.payment_status }})</span>
                </div>
              </div>
            </div>

            <!-- Middle/Right: Compact Status Chips & Escrow Amount & Toggle -->
            <div class="flex items-center justify-between lg:justify-end gap-3 sm:gap-4 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 dark:border-[#30363D]">
              <!-- Items & Inspection & Delivery badges -->
              <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                <span class="px-2 py-0.5 rounded-lg bg-[#F0F1F2] dark:bg-[#21262D] text-[#1E2328] dark:text-[#F0F6FC] font-bold">
                  {{ parseFloat(order.items?.reduce((acc, it) => acc + parseFloat(it.quantity || 0), 0) || order.quantity_kg || 0) }} {{ order.items?.[0]?.listing?.unit || order.items?.[0]?.unit || 'kg' }}
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/50 dark:border-indigo-800/40 capitalize">
                  {{ $t(order.inspection_status || 'pending') }}
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold border border-amber-200/50 dark:border-amber-800/40 capitalize">
                  {{ $t(order.delivery_status || 'admin.awaitingTransport') }}
                </span>
                <span :class="['px-2 py-0.5 rounded-lg font-bold border capitalize', order.payout_status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200/50 dark:border-emerald-800/40' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200/50 dark:border-rose-800/40']">
                  Payout: {{ $t(order.payout_status || 'admin.withheld') }}
                </span>
              </div>

              <!-- Escrow Total Amount -->
              <div class="text-right min-w-[90px]">
                <span class="text-base font-black text-[#1E9444] dark:text-emerald-400 tracking-tight block">
                  {{ formatETB(order.total_amount) }}
                </span>
              </div>

              <!-- Accordion Toggle Button -->
              <button @click="toggleTimeline(order.id)" 
                class="p-1.5 rounded-lg bg-[#F0F1F2] dark:bg-[#21262D] hover:bg-gray-200 dark:hover:bg-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] transition-colors cursor-pointer"
                :title="expandedOrderIds[order.id] ? 'Hide Details' : 'View Timeline & Details'">
                <ChevronDown :class="['w-4 h-4 transition-transform duration-200', expandedOrderIds[order.id] ? 'rotate-180' : '']" />
              </button>
            </div>
          </div>

          <!-- Expandable Details & Timeline Drawer -->
          <div v-if="expandedOrderIds[order.id]" class="mt-3 pt-3 border-t border-gray-100 dark:border-[#30363D] animate-in fade-in duration-200">
            <OrderTimeline :status="order.status" />
          </div>
        </article>
      </div>

      <Pagination 
        :currentPage="currentPage" 
        :totalPages="totalPages" 
        :totalItems="orders.length" 
        :itemsPerPage="itemsPerPage" 
        @update:currentPage="currentPage = $event" 
        @refresh="loadOrders"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ShoppingBag, Loader2, RefreshCcw, CreditCard, User, Tractor, Calendar, ChevronDown } from 'lucide-vue-next'
import { adminApi } from '@/services/adminService'
import { formatETB, formatDate } from '@/utils/helpers'
import OrderTimeline from '@/components/shared/OrderTimeline.vue'
import Pagination from '@/components/common/Pagination.vue'

const orders = ref([])
const isLoading = ref(true)
const expandedOrderIds = ref({})

const toggleTimeline = (orderId) => {
  expandedOrderIds.value[orderId] = !expandedOrderIds.value[orderId]
}

const currentPage = ref(1)
const itemsPerPage = 6

const totalPages = computed(() => Math.ceil(orders.value.length / itemsPerPage) || 1)

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return orders.value.slice(start, start + itemsPerPage)
})

const loadOrders = async () => {
  isLoading.value = true
  try {
    const res = await adminApi.fetchOrders()
    orders.value = res.data || res
  } catch (err) {
    orders.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(loadOrders)

const statusColor = (status) => {
  const map = { 
    pending_payment: 'bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
    placed: 'bg-blue-100 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60', 
    in_transit: 'bg-indigo-100 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60', 
    delivered: 'bg-teal-100 dark:bg-teal-950/50 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60', 
    completed: 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
    cancelled: 'bg-red-100 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60'
  }
  return status ? (map[status] || 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700') : 'bg-gray-100 dark:bg-gray-800'
}
</script>
