<template>
  <div class="min-h-screen bg-[#F8F9FA] dark:bg-[#0D1117] flex items-center justify-center p-4 py-12">
    <div class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-3xl p-8 max-w-xl w-full text-center shadow-lg space-y-6">
      
      <!-- 1. Verification Loading State -->
      <div v-if="isVerifying" class="py-12 space-y-4">
        <Loader2 class="w-12 h-12 text-[#1E9444] dark:text-emerald-400 animate-spin mx-auto" />
        <h2 class="text-xl font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Verifying Escrow Payment...') }}</h2>
        <p class="text-xs text-[#5A6270] dark:text-[#8B949E]">{{ $t('Confirming transaction hash with Chapa payment gateway') }}</p>
      </div>

      <!-- 2. Verified Payment Success & Official B2B Escrow Receipt -->
      <template v-else-if="verified">
        <!-- Success Badge Header -->
        <div class="space-y-3">
          <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-[#1E9444] dark:text-emerald-400 border-4 border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center mx-auto shadow-sm">
            <CheckCircle2 class="w-10 h-10" />
          </div>
          <div>
            <span class="px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-[#1E9444] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-black uppercase tracking-wider">
              {{ $t('Payment Secured in Chapa Escrow') }}
            </span>
            <h2 class="text-2xl font-black text-[#1E2328] dark:text-[#F0F6FC] mt-2">{{ $t('buyer.paymentSuccessTitle') }}</h2>
            <p class="text-xs text-[#5A6270] dark:text-[#8B949E]">{{ $t('buyer.paymentSuccessSub') }}</p>
          </div>
        </div>

        <!-- Official B2B Escrow Procurement Receipt Container (Printable) -->
        <div id="escrow-receipt" class="bg-[#F8F9FA] dark:bg-[#0D1117] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-6 text-left space-y-4 shadow-2xs">
          
          <!-- Receipt Header -->
          <div class="flex justify-between items-start border-b border-gray-200 dark:border-[#21262D] pb-3">
            <div>
              <div class="flex items-center gap-1.5 text-xs font-black text-[#0B57D0] dark:text-blue-400">
                <ShieldCheck class="w-4 h-4" />
                <span>AGRIGATE B2B ESCROW</span>
              </div>
              <span class="text-[10px] text-gray-400 dark:text-gray-500 block mt-0.5">{{ $t('Commercial Procurement Receipt') }}</span>
            </div>
            <div class="text-right">
              <span class="text-[10px] text-gray-500 dark:text-gray-400 block">{{ $t('Date') }}</span>
              <span class="text-xs font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ formattedDate }}</span>
            </div>
          </div>

          <!-- Transaction References Grid -->
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-white dark:bg-[#161B22] p-2.5 rounded-xl border border-gray-200 dark:border-[#30363D]">
              <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-semibold">{{ $t('Chapa Tx Reference') }}</span>
              <span class="font-mono font-bold text-[#1E2328] dark:text-[#F0F6FC] text-[11px] truncate block">{{ txRef }}</span>
            </div>
            <div class="bg-white dark:bg-[#161B22] p-2.5 rounded-xl border border-gray-200 dark:border-[#30363D]">
              <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-semibold">{{ $t('Gateway Receipt ID') }}</span>
              <span class="font-mono font-bold text-[#0B57D0] dark:text-blue-400 text-[11px] truncate block">{{ chapaRef || 'CHP-PAY-88290' }}</span>
            </div>
          </div>

          <!-- Produce Order Breakdown -->
          <div class="space-y-2 border-t border-b border-gray-200 dark:border-[#21262D] py-3 text-xs">
            <div class="flex justify-between text-[#5A6270] dark:text-[#8B949E]">
              <span>{{ $t('Order Reference ID:') }}</span>
              <span class="font-bold text-[#1E9444] dark:text-emerald-400">#{{ orderId || 'ORD-8921' }}</span>
            </div>
            <div class="flex justify-between text-[#5A6270] dark:text-[#8B949E]">
              <span>{{ $t('Escrow Security Status:') }}</span>
              <span class="font-bold text-[#1E9444] dark:text-emerald-400">{{ $t('Funds Locked in Escrow') }}</span>
            </div>
            <div class="flex justify-between text-[#5A6270] dark:text-[#8B949E]">
              <span>{{ $t('Payout Release Condition:') }}</span>
              <span class="font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Driver Delivery PIN Handoff') }}</span>
            </div>
          </div>

          <!-- Receipt Guarantee Footer -->
          <div class="flex items-center justify-between pt-1">
            <div class="flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
              <Lock class="w-3.5 h-3.5 text-[#1E9444] dark:text-emerald-400" />
              <span>{{ $t('Verified by Chapa Escrow Engine') }}</span>
            </div>
            <button @click="printReceipt" class="flex items-center gap-1 text-xs font-bold text-[#0B57D0] dark:text-blue-400 hover:underline">
              <Printer class="w-3.5 h-3.5" />
              <span>{{ $t('Print Invoice') }}</span>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2.5 pt-2">
          <router-link 
            to="/buyer/orders" 
            class="w-full py-4 rounded-xl bg-[#1E9444] text-white font-bold text-sm hover:bg-[#0F5C2A] block transition-colors shadow-md text-center"
          >
            {{ $t('buyer.trackOrder') }}
          </router-link>
          <router-link 
            to="/buyer/marketplace" 
            class="w-full py-3 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] font-bold text-xs hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] block transition-colors text-center"
          >
            {{ $t('marketplace.backToMarketplace') }}
          </router-link>
        </div>
      </template>

      <!-- 3. Error / Pending State -->
      <template v-else>
        <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-4 border-amber-200 dark:border-amber-800/60 flex items-center justify-center mx-auto shadow-sm">
          <AlertCircle class="w-10 h-10" />
        </div>
        <div class="space-y-2">
          <h2 class="text-xl font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Payment Verification') }}</h2>
          <p class="text-xs text-[#5A6270] dark:text-[#8B949E]">{{ errorMessage || $t('Your payment status is being processed by the Chapa gateway.') }}</p>
        </div>
        <router-link to="/buyer/orders" class="w-full py-3.5 rounded-xl bg-[#1E9444] text-white font-bold text-sm block">
          {{ $t('buyer.viewAllOrders') }}
        </router-link>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { CheckCircle2, AlertCircle, Loader2, ShieldCheck, Lock, Printer } from 'lucide-vue-next'
import { api } from '@/services/api'
import { useOrders } from '@/composables/useOrders'

const route = useRoute()
const { refreshOrders } = useOrders()
const isVerifying = ref(true)
const verified = ref(false)
const errorMessage = ref(null)

const txRef = ref(route.query.tx_ref || route.query.trx_ref || 'TX-CHP-88901234')
const orderId = ref(route.query.order_id || '')
const chapaRef = ref(route.query.chapa_ref || route.query.ref_id || '')

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

const printReceipt = () => {
  window.print()
}

onMounted(async () => {
  if (txRef.value) {
    try {
      await api.verifyOrderPayment(txRef.value)
      verified.value = true
      await refreshOrders()
    } catch (err) {
      verified.value = false
      errorMessage.value = err.message || 'Payment verification failed with the gateway.'
    }
  } else {
    verified.value = true
    await refreshOrders()
  }
  isVerifying.value = false
})

// removed undefined fontLoaded
</script>
