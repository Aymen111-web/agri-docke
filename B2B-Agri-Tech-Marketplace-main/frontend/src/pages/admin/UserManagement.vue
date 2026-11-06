<template>
  <div class="space-y-6 lg:space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-[#E2E4E7] dark:border-[#30363D] pb-6 relative">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-1.5">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
            <Users class="w-5 h-5 text-white" />
          </div>
          <h1 class="text-[28px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.systemUsersTitle') }}</h1>
        </div>
        <p class="text-[14px] font-medium text-[#5A6270] dark:text-[#8B949E] max-w-xl">
          {{ $t('admin.systemUsersSub') }}
        </p>
      </div>
      <div class="flex items-center gap-3 z-10">
        <div class="flex items-center bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-xl p-1 shadow-xs">
          <span class="px-3 py-1.5 text-[11px] font-black uppercase text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.totalUsers') }}</span>
          <span class="px-2.5 py-1 bg-[#F0F1F2] dark:bg-[#21262D] text-[#1E2328] dark:text-[#F0F6FC] rounded-lg text-xs font-bold">{{ users.length }}</span>
        </div>
        <button @click="loadUsers" :disabled="isLoading" 
          class="px-4 py-2.5 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] hover:shadow-md transition-all active:scale-95 flex items-center gap-2 group cursor-pointer">
          <RefreshCcw :class="['w-4 h-4 text-[#5A6270] dark:text-[#8B949E] group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors', isLoading && 'animate-spin']" /> 
          {{ $t('admin.refresh') }}
        </button>
      </div>
      <div class="absolute right-0 top-0 w-64 h-32 bg-indigo-50 dark:bg-indigo-950/20 rounded-full blur-[80px] -z-0 opacity-60"></div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="w-16 h-16 rounded-2xl bg-white dark:bg-[#161B22] shadow-xl flex items-center justify-center mb-4 relative overflow-hidden border border-[#E2E4E7]/50 dark:border-[#30363D]">
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-100 dark:from-indigo-950/40 to-transparent opacity-50"></div>
        <Loader2 class="w-8 h-8 text-indigo-500 animate-spin relative z-10" />
      </div>
      <p class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase tracking-wider mt-2">{{ $t('admin.syncingDirectory') }}</p>
      <p class="text-[11px] text-[#9BA1AA] dark:text-[#8B949E] font-bold mt-1">{{ $t('admin.fetchingParticipants') }}</p>
    </div>

    <div v-else-if="users.length === 0" class="text-center py-20 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-[24px] px-8 shadow-sm relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white dark:from-[#21262D] dark:to-[#161B22] opacity-50 z-0 transition-opacity group-hover:opacity-100"></div>
      <div class="relative z-10">
        <div class="w-20 h-20 mx-auto bg-[#F8F9FA] dark:bg-[#21262D] rounded-full flex items-center justify-center shadow-inner mb-4 border border-[#E2E4E7] dark:border-[#30363D]">
          <UserX class="w-10 h-10 text-[#5A6270] dark:text-[#8B949E] opacity-40" />
        </div>
        <h3 class="text-[18px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.noUsersFound') }}</h3>
        <p class="text-[13px] font-medium text-[#5A6270] dark:text-[#8B949E] mt-1.5 max-w-sm mx-auto">
          {{ $t('admin.noUsersSub') }}
        </p>
      </div>
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-5 relative z-10">
        <article v-for="user in paginatedUsers" :key="user.id" 
          :class="['bg-white dark:bg-[#161B22] border lg:border-transparent lg:border-b lg:border-b-[#E2E4E7] dark:lg:border-b-[#30363D] lg:rounded-none rounded-2xl p-5 lg:p-6 shadow-sm lg:shadow-none transition-all group lg:flex lg:items-center lg:justify-between lg:gap-8',
            user.account_status === 'suspended' ? 'opacity-80 lg:hover:bg-red-50 dark:lg:hover:bg-red-950/30 hover:border-red-200 dark:hover:border-red-800/40' : 'lg:hover:bg-[#F8F9FA] dark:lg:hover:bg-[#21262D] hover:shadow-xl hover:border-[#E2E4E7] dark:hover:border-[#30363D]']">
          
          <div class="flex items-start gap-4 lg:gap-5 lg:w-[45%]">
            <div class="relative">
              <div class="w-14 h-14 rounded-full bg-[#F8F9FA] dark:bg-[#21262D] border border-[#E2E4E7] dark:border-[#30363D] flex items-center justify-center text-xl font-black shrink-0 shadow-sm overflow-hidden text-indigo-800 dark:text-indigo-300 bg-gradient-to-br from-indigo-50 dark:from-indigo-950/40 to-violet-100 dark:to-violet-950/40">
                <img v-if="user.profile_photo_url" :src="user.profile_photo_url" class="absolute inset-0 w-full h-full object-cover" />
                <span v-else>{{ user.first_name?.[0] || 'U' }}</span>
              </div>
              <div v-if="user.account_status === 'suspended'" class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-red-100 dark:bg-red-950 flex items-center justify-center border-2 border-white dark:border-[#161B22] shadow-2xs">
                <Ban class="w-3 h-3 text-red-600 dark:text-red-400" />
              </div>
            </div>
            
            <div>
              <div class="flex flex-wrap sm:items-center gap-2">
                <h3 class="text-[17px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                  {{ user.first_name }} {{ user.second_name }}
                </h3>
                
                <div class="flex items-center gap-1.5">
                  <span v-if="user.is_admin" class="inline-flex w-fit px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-fuchsia-50 dark:bg-fuchsia-950/40 text-fuchsia-700 dark:text-fuchsia-300 border border-fuchsia-200 dark:border-fuchsia-800/50 shadow-2xs">{{ $t('nav.roleAdmin') }}</span>
                  <template v-else-if="user.capabilities && user.capabilities.length > 0">
                    <span v-for="cap in user.capabilities" :key="cap.id"
                      :class="['inline-flex w-fit px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider shadow-2xs', 
                        cap.capability_type === 'farmer' ? 'bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 border border-[#C3EFCF] dark:border-emerald-800/50' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/50']">
                      {{ $t(cap.capability_type) }}
                    </span>
                  </template>
                  <span v-else class="inline-flex w-fit px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-gray-100 dark:bg-[#21262D] text-gray-600 dark:text-[#8B949E] border border-gray-200 dark:border-[#30363D] shadow-2xs">{{ $t('admin.unverified') }}</span>
                </div>
              </div>
              
              <div class="flex items-center gap-4 mt-2 text-[12px] font-semibold text-[#5A6270] dark:text-[#8B949E]">
                <span class="flex items-center gap-1.5"><Phone class="w-3.5 h-3.5 opacity-60" /> {{ user.phone || $t('common.notProvided') }}</span>
                <span class="flex items-center gap-1.5"><Calendar class="w-3.5 h-3.5 opacity-60" /> {{ formatDate(user.created_at) }}</span>
              </div>
            </div>
          </div>

          <div class="mt-4 lg:mt-0 lg:w-[25%] flex flex-col justify-center">
            <div class="flex flex-col gap-1">
              <span class="text-[10px] font-black uppercase tracking-wider text-[#9BA1AA] dark:text-[#8B949E]">{{ $t('admin.accountStatus') }}</span>
              <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-bold w-fit shadow-2xs border', 
                user.account_status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/50' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border-red-200/60 dark:border-red-800/50']">
                <CheckCircle2 v-if="user.account_status === 'active'" class="w-3.5 h-3.5" />
                <Ban v-else class="w-3.5 h-3.5" />
                <span class="capitalize">{{ $t(user.account_status || 'active') }}</span>
              </span>
            </div>
          </div>

          <div class="mt-5 pt-5 border-t border-[#E2E4E7] dark:border-[#30363D] lg:border-t-0 lg:mt-0 lg:pt-0 lg:w-[20%] flex lg:justify-end">
            <button v-if="!user.is_admin" @click="toggleStatus(user)" 
              :class="['w-full lg:w-auto px-5 py-2.5 rounded-xl text-[12px] font-bold transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2 cursor-pointer', 
              user.account_status === 'active' ? 'bg-white dark:bg-[#161B22] border-2 border-red-100 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 hover:border-red-200 dark:hover:border-red-800' : 'bg-[#1E9444] text-white hover:bg-[#0F5C2A] shadow-emerald-500/20']">
              <template v-if="user.account_status === 'active'">
                <Lock class="w-4 h-4" /> {{ $t('admin.suspend') }}
              </template>
              <template v-else>
                <Unlock class="w-4 h-4" /> {{ $t('admin.activate') }}
              </template>
            </button>
          </div>
        </article>
      </div>

      <!-- Pagination Controls -->
      <Pagination 
        :currentPage="currentPage" 
        :totalPages="totalPages" 
        :totalItems="users.length" 
        :itemsPerPage="itemsPerPage" 
        @update:currentPage="currentPage = $event" 
        @refresh="loadUsers"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Loader2, Users, RefreshCcw, UserX, Phone, Calendar, Ban, CheckCircle2, Lock, Unlock } from 'lucide-vue-next'
import { adminApi } from '@/services/adminService'
import { useAlertModal } from '@/composables/useAlertModal'
import { formatDate } from '@/utils/helpers'
import Pagination from '@/components/common/Pagination.vue'

const { showAlert } = useAlertModal()
const users = ref([])
const isLoading = ref(true)

const currentPage = ref(1)
const itemsPerPage = 6

const totalPages = computed(() => Math.ceil(users.value.length / itemsPerPage) || 1)

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return users.value.slice(start, start + itemsPerPage)
})

const loadUsers = async () => {
  isLoading.value = true
  try {
    const res = await adminApi.fetchUsers()
    users.value = res.data || res
  } catch (err) {
    users.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(loadUsers)

const toggleStatus = async (user) => {
  try {
    if (user.account_status === 'active') {
      await adminApi.suspendUser(user.id)
    } else {
      await adminApi.activateUser(user.id)
    }
    loadUsers()
  } catch (err) {
    showAlert({
      title: 'User Management Error',
      message: err.message || 'Failed to modify user access',
      type: 'error'
    })
  }
}
</script>
