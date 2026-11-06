<template>
  <div class="relative" ref="bellRef">
    <!-- Top Header Bell Trigger -->
    <button @click="isOpen = !isOpen"
      :class="['relative p-2 rounded-xl border transition-all duration-200 cursor-pointer shadow-xs flex items-center justify-center',
        variant === 'farmer' ? 'bg-white/10 hover:bg-white/20 border-white/20 text-white' : 'bg-gray-50 dark:bg-[#21262D] hover:bg-gray-100 dark:hover:bg-[#30363D] border-gray-200 dark:border-[#30363D] text-[#1E2328] dark:text-[#F0F6FC]']"
      :title="t('Notifications & Action Center')">
      <Bell class="w-5 h-5" />
      
      <!-- Unread Counter Badge -->
      <span v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 px-1.5 py-0.2 min-w-[18px] h-[18px] text-[10px] font-black bg-rose-600 text-white rounded-full flex items-center justify-center shadow-sm border border-white dark:border-[#161B22] animate-pulse">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Drawer Popover -->
    <Transition name="slide">
      <div v-if="isOpen"
        class="absolute right-0 top-full mt-2.5 w-80 sm:w-96 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] shadow-2xl rounded-2xl p-0 z-50 text-[#1E2328] dark:text-[#F0F6FC] overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Arrow pointing to bell -->
        <div class="absolute -top-1.5 right-3.5 w-3 h-3 bg-white dark:bg-[#161B22] border-t border-l border-[#E2E4E7] dark:border-[#30363D] rotate-45 z-10" />

        <!-- Drawer Header -->
        <div class="p-3.5 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-[#062E15] dark:to-[#093d1c] border-b border-[#E2E4E7] dark:border-[#30363D] flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-[#1E9444] text-white flex items-center justify-center">
              <BellRing class="w-4 h-4" />
            </div>
            <div>
              <h4 class="text-xs font-black text-[#0F5C2A] dark:text-[#34D399] tracking-tight">Notifications & Action Center</h4>
              <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Disputes, verdicts & order updates</p>
            </div>
          </div>
          
          <button v-if="unreadCount > 0" @click="markAllAsRead"
            class="text-[10px] font-bold text-[#1E9444] dark:text-[#34D399] hover:underline flex items-center gap-1 cursor-pointer">
            <CheckCheck class="w-3 h-3" />
            <span>Mark all read</span>
          </button>
        </div>

        <!-- Notification Category Tabs -->
        <div class="flex items-center border-b border-gray-100 dark:border-[#30363D] bg-gray-50/50 dark:bg-[#0D1117]/50 px-2 py-1">
          <button v-for="tab in ['all', 'disputes', 'orders']" :key="tab"
            @click="activeTab = tab"
            :class="['px-3 py-1 rounded-lg text-[11px] font-bold capitalize transition-colors cursor-pointer',
              activeTab === tab ? 'bg-white dark:bg-[#21262D] text-[#1E9444] dark:text-[#34D399] shadow-2xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white']">
            {{ tab }}
          </button>
        </div>

        <!-- Notification Cards List -->
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-[#30363D]">
          <div v-if="filteredNotifications.length === 0" class="p-8 text-center space-y-2">
            <CheckCircle2 class="w-8 h-8 mx-auto text-emerald-500 opacity-60" />
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">All caught up! No active notifications.</p>
          </div>

          <div v-for="notif in filteredNotifications" :key="notif.id"
            @click="handleNotificationClick(notif)"
            :class="['p-3.5 transition-colors relative space-y-2 cursor-pointer',
              !notif.isRead ? 'bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-50/70 dark:hover:bg-amber-950/30' : 'hover:bg-gray-50 dark:hover:bg-[#21262D]/60']">
            
            <!-- Item Header -->
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-2">
                <ShieldAlert v-if="notif.type === 'dispute'" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
                <Award v-else-if="notif.type === 'resolution'" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <Package v-else class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" />
                
                <span class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] leading-snug">{{ notif.title }}</span>
              </div>
              <span class="text-[9px] font-semibold text-gray-400 shrink-0">{{ formatDate(notif.timestamp) }}</span>
            </div>

            <!-- Item Body Message -->
            <p class="text-xs text-gray-600 dark:text-[#8B949E] pl-6 leading-normal">
              {{ notif.message }}
            </p>

            <!-- Farmer Counter-Statement Display inside Notification -->
            <div v-if="notif.farmerResponse" class="ml-6 p-2 bg-white dark:bg-[#0D1117] rounded-lg border border-amber-200 dark:border-amber-900/40 text-[11px]">
              <span class="font-bold text-amber-800 dark:text-amber-300 block text-[9px] uppercase">Your Submitted Response:</span>
              <p class="text-[#1E2328] dark:text-[#F0F6FC] font-medium">{{ notif.farmerResponse }}</p>
            </div>

            <!-- Direct Quick Reply Input inside Notification Drawer -->
            <div v-if="notif.requiresResponse && activeReplyNotifId === notif.id" class="ml-6 pt-1 space-y-1.5" @click.stop>
              <textarea v-model="quickReplyText" rows="2" placeholder="Write counter-statement to admin..."
                class="w-full p-2 bg-white dark:bg-[#0D1117] border border-amber-300 dark:border-amber-800 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-amber-500 text-[#1E2328] dark:text-[#F0F6FC]"></textarea>
              <div class="flex items-center justify-end gap-2">
                <button @click="activeReplyNotifId = null" class="px-2 py-1 text-[10px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                <button @click="handleQuickReplySubmit(notif)" :disabled="isSubmittingReply"
                  class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-black flex items-center gap-1 shadow-2xs disabled:opacity-50 cursor-pointer">
                  <Loader2 v-if="isSubmittingReply" class="w-3 h-3 animate-spin" />
                  <Send v-else class="w-3 h-3" />
                  <span>Send Reply</span>
                </button>
              </div>
            </div>

            <!-- Action Bar inside Notification Item -->
            <div class="ml-6 flex items-center justify-between pt-1">
              <button v-if="notif.requiresResponse && activeReplyNotifId !== notif.id"
                @click.stop="activeReplyNotifId = notif.id; quickReplyText = ''"
                class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-[10px] font-black flex items-center gap-1 cursor-pointer">
                <MessageSquare class="w-3 h-3" />
                <span>Reply to Admin</span>
              </button>
              
              <button @click.stop="navigateToOrder(notif)"
                class="text-[10px] font-bold text-[#1E9444] dark:text-[#34D399] hover:underline flex items-center gap-0.5 ml-auto cursor-pointer">
                <span>View Order #{{ notif.displayId }}</span>
                <ExternalLink class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <!-- Drawer Footer -->
        <div class="p-2 bg-gray-50 dark:bg-[#0D1117] border-t border-[#E2E4E7] dark:border-[#30363D] text-center">
          <span class="text-[10px] text-gray-400 font-semibold">Qelem Meda Arbitration & Escalation System</span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, BellRing, ShieldAlert, Award, Package, CheckCircle2, CheckCheck, ExternalLink, MessageSquare, Send, Loader2 } from 'lucide-vue-next'
import { useNotifications } from '@/composables/useNotifications'
import { useLanguage } from '@/composables/useLanguage'
import { useAlertModal } from '@/composables/useAlertModal'
import { formatDate } from '@/utils/helpers'

const props = defineProps({
  variant: { type: String, default: 'default' }
})

const router = useRouter()
const { t } = useLanguage()
const { showAlert } = useAlertModal()
const { notifications, unreadCount, markAsRead, markAllAsRead, submitDisputeReplyFromNotification } = useNotifications()

const isOpen = ref(false)
const activeTab = ref('all')
const bellRef = ref(null)
const activeReplyNotifId = ref(null)
const quickReplyText = ref('')
const isSubmittingReply = ref(false)

const filteredNotifications = computed(() => {
  if (activeTab.value === 'disputes') {
    return notifications.value.filter(n => n.type === 'dispute' || n.type === 'resolution' || n.type === 'farmer_response')
  }
  if (activeTab.value === 'orders') {
    return notifications.value.filter(n => n.type === 'order')
  }
  return notifications.value
})

const handleNotificationClick = (notif) => {
  markAsRead(notif.id)
}

const navigateToOrder = (notif) => {
  markAsRead(notif.id)
  isOpen.value = false
  const path = router.currentRoute.value.path.startsWith('/farmer') ? '/farmer/orders' : '/buyer/orders'
  router.push(path)
}

const handleQuickReplySubmit = async (notif) => {
  if (!quickReplyText.value.trim()) {
    showAlert({ title: 'Reply Required', message: 'Please enter your defense or reply statement.', type: 'warning' })
    return
  }

  isSubmittingReply.value = true
  try {
    await submitDisputeReplyFromNotification(notif.disputeId, quickReplyText.value.trim())
    showAlert({ title: 'Reply Submitted', message: 'Your counter-statement has been sent to the Admin.', type: 'success' })
    activeReplyNotifId.value = null
    quickReplyText.value = ''
    markAsRead(notif.id)
  } catch (err) {
    showAlert({ title: 'Error', message: err.message || 'Failed to submit reply.', type: 'error' })
  } finally {
    isSubmittingReply.value = false
  }
}

const handleClickOutside = (event) => {
  if (bellRef.value && !bellRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: all 0.2s ease;
}
.slide-enter-from, .slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
