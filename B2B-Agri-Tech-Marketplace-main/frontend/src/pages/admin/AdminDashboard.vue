<template>
  <div class="space-y-6 lg:space-y-8 animate-in fade-in duration-500">
    <!-- Notifications -->
    <div v-if="actionMessage" class="p-4 bg-[#EDFAF2] dark:bg-emerald-950/40 border border-[#C3EFCF] dark:border-emerald-800/50 text-[#0F5C2A] dark:text-emerald-300 rounded-2xl font-bold text-[13px] flex items-center justify-between shadow-xs backdrop-blur-md">
      <span class="flex items-center gap-2.5">
        <div class="w-6 h-6 rounded-full bg-[#1E9444] text-white flex items-center justify-center text-xs font-black">
          <Check class="w-3.5 h-3.5" />
        </div>
        {{ actionMessage }}
      </span>
      <button @click="actionMessage = null" class="text-xs font-bold hover:underline cursor-pointer opacity-80 hover:opacity-100">{{ $t('admin.dismiss') }}</button>
    </div>

    <div v-if="error" class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 rounded-2xl font-bold text-[13px] flex items-center justify-between shadow-xs backdrop-blur-md">
      <span class="flex items-center gap-2.5">
        <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0" />
        {{ error }}
      </span>
      <button @click="loadDashboardData" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">{{ $t('admin.retry') }}</button>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-[#E2E4E7] dark:border-[#30363D] pb-6 relative overflow-hidden">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-[#1E9444] text-white flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-4 ring-indigo-50 dark:ring-indigo-950/40">
            <Activity class="w-5 h-5" />
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.commandCenter') }}</h1>
          </div>
        </div>
        <p class="text-[14px] font-medium text-[#5A6270] dark:text-[#8B949E] max-w-2xl leading-relaxed">
          {{ $t('admin.commandCenterSub') }}
        </p>
      </div>

      <div class="flex items-center gap-3 z-10 shrink-0">
        <button @click="loadDashboardData" :disabled="isLoading" 
          class="px-4 py-2.5 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] hover:shadow-md transition-all active:scale-95 flex items-center gap-2 group cursor-pointer">
          <RefreshCcw :class="['w-4 h-4 text-[#5A6270] dark:text-[#8B949E] group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors', isLoading && 'animate-spin']" /> 
          <span>{{ $t('admin.refresh') }}</span>
        </button>
      </div>

      <div class="absolute right-0 top-0 w-80 h-36 bg-gradient-to-l from-indigo-500/10 via-emerald-500/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 bg-white/50 dark:bg-[#161B22]/50 border border-gray-100 dark:border-[#30363D] rounded-3xl backdrop-blur-xs">
      <div class="w-16 h-16 rounded-2xl bg-white dark:bg-[#161B22] shadow-xl flex items-center justify-center mb-4 relative overflow-hidden border border-[#E2E4E7]/60 dark:border-[#30363D]">
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-100 dark:from-indigo-950/40 to-transparent opacity-50"></div>
        <Loader2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400 animate-spin relative z-10" />
      </div>
      <p class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase tracking-wider mt-2">{{ $t('admin.connectingDataGrid') }}</p>
      <p class="text-[12px] text-[#9BA1AA] dark:text-[#8B949E] font-bold mt-1">{{ $t('admin.aggregatingMetrics') }}</p>
    </div>

    <template v-else>
      <!-- Integrated Top System Executive KPI Stats Container -->
      <div class="space-y-5">
        <!-- Main 4 KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Card 1: GMV Facilitated -->
          <div class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-5 shadow-2xs hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between space-y-4 group">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#1E9444]" />
            <div>
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-[11px] font-black text-[#5A6270] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.gmvFacilitated') }}</span>
                  <h3 class="text-2xl sm:text-3xl font-black text-[#1E9444] dark:text-emerald-400 mt-2 tracking-tight">
                    {{ formatETB(kpis?.total_gmv || 0) }}
                  </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-[#1E9444] dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <DollarSign class="w-5 h-5" />
                </div>
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100 dark:border-[#30363D] flex items-center justify-between text-[11px] font-bold text-[#5A6270] dark:text-[#8B949E]">
              <span class="flex items-center gap-1.5 text-[#1E9444] dark:text-emerald-400 font-bold">
                <TrendingUp class="w-3.5 h-3.5" /> {{ kpis?.total_orders || 0 }} {{ $t('admin.totalOrdersMetric') }}
              </span>
              <router-link to="/admin/orders" class="text-[#1E9444] dark:text-emerald-400 hover:underline flex items-center gap-1 font-bold">
                <span>{{ $t('admin.viewOrders') }}</span>
                <ArrowRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"/>
              </router-link>
            </div>
          </div>

          <!-- Card 2: Verification Requests -->
          <div class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-5 shadow-2xs hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between space-y-4 group">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#F5B73A]" />
            <div>
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-[11px] font-black text-[#5A6270] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.verification') }}</span>
                  <h3 class="text-2xl sm:text-3xl font-black text-[#1E2328] dark:text-[#F0F6FC] mt-2 tracking-tight">
                    {{ kpis?.pending_applications || 0 }}
                  </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-[#F5B73A] dark:text-amber-300 border border-amber-100 dark:border-amber-800/60 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <FileCheck class="w-5 h-5" />
                </div>
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100 dark:border-[#30363D] flex items-center justify-between text-[11px] font-bold">
              <span class="text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1">
                <AlertTriangle class="w-3.5 h-3.5" /> {{ $t('admin.actionRequired') }}
              </span>
              <router-link to="/admin/applications" class="text-amber-800 dark:text-amber-300 hover:underline flex items-center gap-1 font-bold">
                <span>{{ $t('admin.review') }}</span>
                <ArrowRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"/>
              </router-link>
            </div>
          </div>

          <!-- Card 3: Active Produce Listings -->
          <div class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-5 shadow-2xs hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between space-y-4 group">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#14B8A6]" />
            <div>
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-[11px] font-black text-[#5A6270] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.activeListings') }}</span>
                  <h3 class="text-2xl sm:text-3xl font-black text-[#1E2328] dark:text-[#F0F6FC] mt-2 tracking-tight">
                    {{ kpis?.active_listings || 0 }}
                  </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-[#14B8A6] dark:text-teal-400 border border-teal-100 dark:border-teal-800/60 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <ListOrdered class="w-5 h-5" />
                </div>
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100 dark:border-[#30363D] flex items-center justify-between text-[11px] font-bold">
              <span class="text-teal-700 dark:text-teal-400 font-bold flex items-center gap-1">
                <ShoppingCart class="w-3.5 h-3.5" /> {{ $t('admin.liveMarket') }}
              </span>
              <router-link to="/admin/listings" class="text-teal-700 dark:text-teal-300 hover:underline flex items-center gap-1 font-bold">
                <span>{{ $t('admin.moderate') }}</span>
                <ArrowRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"/>
              </router-link>
            </div>
          </div>

          <!-- Card 4: Escrow Disputes -->
          <div class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-5 shadow-2xs hover:shadow-xl transition-all duration-300 relative overflow-hidden flex flex-col justify-between space-y-4 group">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#E6533C]" />
            <div>
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-[11px] font-black text-[#5A6270] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.escrowDisputes') }}</span>
                  <h3 class="text-2xl sm:text-3xl font-black text-[#E6533C] dark:text-red-400 mt-2 tracking-tight">
                    {{ kpis?.payment_exceptions_count || 0 }}
                  </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#E6533C] dark:text-red-400 border border-red-100 dark:border-red-800/60 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <ShieldAlert class="w-5 h-5" />
                </div>
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100 dark:border-[#30363D] flex items-center justify-between text-[11px] font-bold">
              <span class="text-red-600 dark:text-red-400 font-bold flex items-center gap-1">
                <Lock class="w-3.5 h-3.5" /> {{ $t('admin.fundsHeld') }}
              </span>
              <router-link to="/admin/disputes" class="text-red-600 dark:text-red-400 hover:underline flex items-center gap-1 font-bold">
                <span>{{ $t('admin.arbitrage') }}</span>
                <ArrowRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"/>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Joined Secondary System Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] p-1.5 rounded-2xl shadow-2xs">
          <div class="flex items-center gap-4 p-4 hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-default">
            <div class="w-10 h-10 rounded-xl bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 flex items-center justify-center shrink-0 border border-[#C3EFCF] dark:border-emerald-800/50">
              <Users class="w-5 h-5 text-[#1E9444] dark:text-emerald-400" />
            </div>
            <div>
              <span class="text-[11px] font-black text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.verifiedEcosystem') }}</span>
              <p class="text-[14px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] mt-0.5">
                {{ kpis?.total_farmers || 0 }} {{ $t('admin.farmersCount') }} · {{ kpis?.total_buyers || 0 }} {{ $t('admin.buyersCount') }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-4 p-4 border-t sm:border-t-0 sm:border-l border-[#E2E4E7]/60 dark:border-[#30363D] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-default">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-800/50">
              <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
              <span class="text-[11px] font-black text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.farmerPayouts') }}</span>
              <p class="text-[14px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] mt-0.5">
                {{ formatETB(kpis?.pending_payouts_amount || 0) }} {{ $t('admin.pending') }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-4 p-4 border-t sm:border-t-0 sm:border-l border-[#E2E4E7]/60 dark:border-[#30363D] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-default">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 flex items-center justify-center shrink-0 border border-blue-200 dark:border-blue-800/50">
              <ShoppingBag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <span class="text-[11px] font-black text-[#9BA1AA] dark:text-[#8B949E] uppercase tracking-wider">{{ $t('admin.systemOrders') }}</span>
              <p class="text-[14px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] mt-0.5">
                {{ kpis?.total_orders || 0 }} {{ $t('admin.completed') }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Approvals Queue and Audit Trail Section -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Pending Verification Applications Card -->
        <div class="lg:col-span-7 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl shadow-2xs overflow-hidden flex flex-col">
          <div class="p-5 border-b border-[#E2E4E7]/60 dark:border-[#30363D] flex items-center justify-between bg-[#F8F9FA] dark:bg-[#21262D]">
            <div>
              <h2 class="text-base font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.pendingActionQueue') }}</h2>
              <p class="text-[11px] font-semibold text-[#9BA1AA] dark:text-[#8B949E] mt-0.5">{{ $t('admin.pendingActionQueueSub') }}</p>
            </div>
            <router-link to="/admin/applications" class="text-xs font-bold text-[#1E9444] dark:text-emerald-400 hover:bg-[#EDFAF2] dark:hover:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-transparent hover:border-[#C3EFCF] dark:hover:border-emerald-800/50 transition-all">
              {{ $t('admin.viewAllCount', { count: kpis?.pending_applications || 0 }) }}
            </router-link>
          </div>
          
          <div v-if="pendingApprovals.length === 0" class="p-12 text-center text-[#5A6270] dark:text-[#8B949E] my-auto flex flex-col items-center justify-center">
            <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center mb-3 border border-emerald-100 dark:border-emerald-800/50 shadow-xs">
              <FileCheck class="w-7 h-7 text-[#1E9444] dark:text-emerald-400" />
            </div>
            <h3 class="text-sm font-extrabold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.clearQueue') }}</h3>
            <p class="text-xs font-medium mt-1 text-[#5A6270] dark:text-[#8B949E]">{{ $t('admin.clearQueueSub') }}</p>
          </div>
          
          <div v-else class="divide-y divide-[#E2E4E7]/60 dark:divide-[#30363D]">
            <div v-for="app in pendingApprovals" :key="app.id" class="p-4 flex items-center justify-between hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] transition-colors group">
              <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#1E9444] dark:text-emerald-400 flex items-center justify-center text-xs font-black border border-[#C3EFCF] dark:border-emerald-800/50 shrink-0">
                  {{ app.user?.first_name?.[0] || 'A' }}
                </div>
                <div>
                  <div class="flex flex-wrap items-center gap-2 mb-0.5">
                    <h4 class="text-xs font-extrabold text-[#1E2328] dark:text-[#F0F6FC]">{{ app.user?.first_name }} {{ app.user?.second_name }}</h4>
                    <span :class="['px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider', app.capability_type === 'farmer' ? 'bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 border border-[#C3EFCF] dark:border-emerald-800/50' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50']">
                      {{ app.capability_type === 'farmer' ? $t('admin.farmerRequest') : $t('admin.buyerRequest') }}
                    </span>
                  </div>
                  <p class="text-[11px] font-medium text-[#5A6270] dark:text-[#8B949E] flex items-center gap-2">
                    <span class="flex items-center gap-1"><Phone class="w-3 h-3 text-emerald-600 dark:text-emerald-400" /> {{ app.user?.phone || 'N/A' }}</span>
                    <span class="text-gray-300 dark:text-gray-600">•</span>
                    <span>{{ formatDate(app.created_at) }}</span>
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button @click="handleQuickApprove(app.id)" class="px-3 py-1.5 rounded-xl bg-[#1E9444] hover:bg-[#0F5C2A] text-white font-bold text-[11px] flex items-center gap-1 shadow-2xs transition-all cursor-pointer">
                  <Check class="w-3.5 h-3.5" /> {{ $t('admin.approve') }}
                </button>
                <button @click="handleQuickReject(app.id)" class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#161B22] border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 font-bold text-[11px] hover:bg-red-50 dark:hover:bg-red-950/40 transition-all shadow-2xs cursor-pointer">
                  {{ $t('admin.reject') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Audit Trail Feed Card -->
        <div class="lg:col-span-5 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl shadow-2xs overflow-hidden flex flex-col">
          <div class="p-5 border-b border-[#E2E4E7]/60 dark:border-[#30363D] flex items-center justify-between bg-[#F8F9FA] dark:bg-[#21262D]">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-center shrink-0">
                <Activity class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
              </div>
              <div>
                <h2 class="text-base font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.auditTrailLog') }}</h2>
                <p class="text-[10px] font-black uppercase text-[#9BA1AA] dark:text-[#8B949E] tracking-wider mt-0.5">{{ $t('admin.automatedEventTracking') }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 overflow-y-auto max-h-[420px] relative scrollbar-hide">
            <p v-if="recentActivity.length === 0" class="text-center text-xs font-bold text-[#9BA1AA] dark:text-[#8B949E] py-10">{{ $t('admin.noRecentEvents') }}</p>
            
            <div class="relative z-10 flex flex-col gap-3">
              <div v-for="log in recentActivity" :key="log.id" class="p-3 bg-[#F8F9FA] dark:bg-[#21262D] rounded-xl border border-[#E2E4E7]/60 dark:border-[#30363D] hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors">
                <div class="flex items-center justify-between gap-1 mb-1">
                  <span class="text-xs font-extrabold text-[#1E2328] dark:text-[#F0F6FC] truncate flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    {{ log.action }}
                  </span>
                  <span class="text-[10px] font-mono text-[#9BA1AA] dark:text-[#8B949E] shrink-0">{{ formatDate(log.created_at) }}</span>
                </div>
                <p class="text-[11px] text-[#5A6270] dark:text-[#8B949E] leading-relaxed">
                  {{ $t('admin.byUser') }} <strong class="text-[#1E2328] dark:text-[#F0F6FC]">{{ log.user?.name || $t('admin.systemAuto') }}</strong> · {{ $t('admin.resource') }} 
                  <span class="font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/50 px-1.5 py-0.2 rounded text-[10px] border border-indigo-100 dark:border-indigo-800/40">
                    {{ log.auditable_type || $t('admin.unknown') }} #{{ log.auditable_id }}
                  </span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { FileCheck, ListOrdered, ShoppingBag, DollarSign, ShieldAlert, TrendingUp, Check, Activity, ArrowRight, Users, Clock, AlertTriangle, Loader2, RefreshCcw, Lock, ShoppingCart, Phone } from 'lucide-vue-next'
import { adminApi } from '@/services/adminService'
import { formatETB, formatDate } from '@/utils/helpers'
import { useLanguage } from '@/composables/useLanguage'
import { useAlertModal } from '@/composables/useAlertModal'

const { t } = useLanguage()
const { showAlert } = useAlertModal()

const kpis = ref(null)
const recentActivity = ref([])
const pendingApprovals = ref([])
const isLoading = ref(true)
const error = ref(null)
const actionMessage = ref(null)

const loadDashboardData = async () => {
  isLoading.value = true
  error.value = null
  try {
    const data = await adminApi.fetchDashboardStats()
    kpis.value = data.kpis
    recentActivity.value = data.recent_activity || []
    pendingApprovals.value = data.pending_approvals_preview || []
  } catch (err) {
    error.value = err.message || 'Failed to load dashboard metrics.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadDashboardData)

const handleQuickApprove = async (id) => {
  try {
    await adminApi.approveApplication(id)
    actionMessage.value = t('admin.verificationApproved')
    setTimeout(() => actionMessage.value = null, 4000)
    loadDashboardData()
  } catch (err) {
    showAlert({
      title: 'Approval Error',
      message: err.message || 'Failed to approve application.',
      type: 'error'
    })
  }
}

const handleQuickReject = async (id) => {
  const reason = prompt(t('admin.specifyRejectionReason'))
  if (reason === null) return
  try {
    await adminApi.rejectApplication(id, reason || t('admin.docRequirementsNotMet'))
    actionMessage.value = t('admin.verificationDenied')
    setTimeout(() => actionMessage.value = null, 4000)
    loadDashboardData()
  } catch (err) {
    showAlert({
      title: 'Rejection Error',
      message: err.message || 'Failed to reject application.',
      type: 'error'
    })
  }
}
</script>
