<template>
  <div class="space-y-6 lg:space-y-8 animate-in fade-in duration-500">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-[#E2E4E7] dark:border-[#30363D] pb-6 relative">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-1.5">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#1E9444] to-[#0F5C2A] flex items-center justify-center shadow-lg shadow-emerald-500/20">
            <FileText class="w-5 h-5 text-white" />
          </div>
          <h1 class="text-[28px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.capabilitiesVerificationQueue') }}</h1>
        </div>
        <p class="text-[14px] font-medium text-[#5A6270] dark:text-[#8B949E] max-w-xl">
          {{ $t('admin.capabilitiesVerificationSub') }}
        </p>
      </div>
      <div class="flex items-center gap-3 z-10">
        <div class="flex items-center bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-xl p-1 shadow-xs">
          <span class="px-3 py-1.5 text-[11px] font-black uppercase text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.pendingQueue') }}</span>
          <span class="px-2.5 py-1 bg-[#F0F1F2] dark:bg-[#21262D] text-[#1E2328] dark:text-[#F0F6FC] rounded-lg text-xs font-bold">{{ applications.length }}</span>
        </div>
        <button @click="loadApplications" :disabled="isLoading" 
          class="px-4 py-2.5 rounded-xl border border-[#E2E4E7] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[13px] font-bold text-[#1E2328] dark:text-[#F0F6FC] hover:bg-[#F8F9FA] dark:hover:bg-[#21262D] hover:shadow-md transition-all active:scale-95 flex items-center gap-2 group cursor-pointer">
          <RefreshCcw :class="['w-4 h-4 text-[#5A6270] dark:text-[#8B949E] group-hover:text-[#1E9444] dark:group-hover:text-emerald-400 transition-colors', isLoading && 'animate-spin']" /> 
          {{ $t('admin.refresh') }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="w-16 h-16 rounded-2xl bg-white dark:bg-[#161B22] shadow-xl flex items-center justify-center mb-4 relative overflow-hidden border border-[#E2E4E7]/50 dark:border-[#30363D]">
        <Loader2 class="w-8 h-8 text-[#1E9444] dark:text-emerald-400 animate-spin relative z-10" />
      </div>
      <p class="text-xs font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase tracking-wider mt-2">{{ $t('admin.syncingVerificationQueue') }}</p>
      <p class="text-[11px] text-[#9BA1AA] dark:text-[#8B949E] font-bold mt-1">{{ $t('admin.fetchingApplications') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="applications.length === 0" class="text-center py-20 bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-[24px] px-8 shadow-sm">
      <div class="w-20 h-20 mx-auto bg-[#F8F9FA] dark:bg-[#21262D] rounded-full flex items-center justify-center shadow-inner mb-4 border border-[#E2E4E7] dark:border-[#30363D]">
        <CheckCircle class="w-10 h-10 text-[#5A6270] dark:text-[#8B949E] opacity-40" />
      </div>
      <h3 class="text-[18px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">{{ $t('admin.queueIsEmpty') }}</h3>
      <p class="text-[13px] font-medium text-[#5A6270] dark:text-[#8B949E] mt-1.5 max-w-sm mx-auto">
        {{ $t('admin.queueIsEmptySub') }}
      </p>
    </div>

    <!-- Clean Single-Row Applications List -->
    <div v-else class="space-y-4">
      <div class="space-y-3">
        <article v-for="app in paginatedApplications" :key="app.id" 
          class="bg-white dark:bg-[#161B22] border border-[#E2E4E7] dark:border-[#30363D] rounded-2xl p-4 shadow-2xs hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
          
          <!-- One-Row Applicant Meta (Name, Capability Badge, Phone, Date) -->
          <div class="flex items-center gap-3.5 flex-1 flex-wrap min-w-0">
            <div :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm border shadow-xs shrink-0', 
              app.capability_type === 'farmer' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 border-[#C3EFCF] dark:border-emerald-800/50' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800/50'
            ]">
              {{ app.user?.first_name?.[0] || app.user?.name?.[0] || 'U' }}
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
              <h3 class="text-[15px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] truncate">
                {{ getApplicantName(app) }}
              </h3>

              <span :class="[
                'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shrink-0', 
                app.capability_type === 'farmer' ? 'bg-[#EDFAF2] dark:bg-emerald-950/40 text-[#0F5C2A] dark:text-emerald-300 border border-[#C3EFCF] dark:border-emerald-800/50' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50'
              ]">
                {{ app.capability_type === 'farmer' ? $t('admin.farmerProducerRequest') : $t('admin.commercialBuyerRequest') }}
              </span>

              <span class="text-gray-300 dark:text-gray-600 hidden md:inline">•</span>

              <span class="text-xs text-[#5A6270] dark:text-[#8B949E] font-semibold flex items-center gap-1 shrink-0">
                <Phone class="w-3.5 h-3.5 text-[#1E9444] dark:text-emerald-400" />
                <span>{{ app.user?.phone || $t('common.notProvided') }}</span>
              </span>

              <span class="text-gray-300 dark:text-gray-600 hidden md:inline">•</span>

              <span class="text-xs text-[#5A6270] dark:text-[#8B949E] font-semibold flex items-center gap-1 shrink-0">
                <Calendar class="w-3.5 h-3.5 text-gray-400" />
                <span>{{ $t('admin.submitted') }} {{ formatDate(app.created_at) }}</span>
              </span>

              <template v-if="getDocsList(app).length > 0">
                <span class="text-gray-300 dark:text-gray-600 hidden md:inline">•</span>
                <span class="text-xs font-semibold flex items-center gap-1 shrink-0">
                  <Paperclip class="w-3.5 h-3.5 text-[#0B57D0] dark:text-blue-400" />
                  <button 
                    @click.stop="openDoc(getDocsList(app)[0], app)" 
                    class="text-[#0B57D0] dark:text-blue-400 hover:text-[#0842A0] dark:hover:text-blue-300 font-extrabold flex items-center gap-1 cursor-pointer bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800/50 transition-colors hover:bg-blue-100 dark:hover:bg-blue-900/40"
                    title="Click to view attached document in new window"
                  >
                    <span>{{ getDocName(getDocsList(app)[0]) }}</span>
                    <ExternalLink class="w-3 h-3" />
                  </button>
                </span>
              </template>
            </div>
          </div>

          <!-- Action Buttons (Audit Specifications, Reject, Approve) -->
          <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
            <template v-if="app.status === 'pending'">
              <button @click="openDetailModal(app)" 
                class="px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-[#21262D] hover:bg-gray-200 dark:hover:bg-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] text-xs font-extrabold transition-colors flex items-center gap-1.5 cursor-pointer border border-gray-200 dark:border-[#30363D]">
                <Eye class="w-3.5 h-3.5 text-[#0B57D0] dark:text-blue-400" />
                <span>{{ $t('admin.auditSpecifications') }}</span>
              </button>
              <button @click="openRejectModal(app)" 
                class="px-3.5 py-2 rounded-xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 text-xs font-extrabold transition-colors cursor-pointer">
                {{ $t('admin.reject') }}
              </button>
              <button @click="handleApprove(app.id)" 
                class="px-4 py-2 rounded-xl bg-[#1E9444] hover:bg-[#0F5C2A] text-white text-xs font-black transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                <Check class="w-4 h-4" />
                <span>{{ $t('admin.approve') }}</span>
              </button>
            </template>
            <template v-else>
              <button @click="openDetailModal(app)" 
                class="px-3.5 py-1.5 rounded-xl bg-gray-50 dark:bg-[#21262D] hover:bg-gray-100 dark:hover:bg-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] text-xs font-bold transition-colors flex items-center gap-1 border border-gray-200 dark:border-[#30363D] cursor-pointer">
                <Eye class="w-3.5 h-3.5 text-gray-500" />
                <span>{{ $t('admin.viewAudit') }}</span>
              </button>
              <span :class="[
                'px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider flex items-center gap-1.5',
                app.status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/50'
              ]">
                <CheckCircle2 v-if="app.status === 'approved'" class="w-3.5 h-3.5" />
                <XCircle v-else class="w-3.5 h-3.5" />
                <span>{{ $t(app.status) }}</span>
              </span>
            </template>
          </div>
        </article>
      </div>

      <!-- Pagination Controls -->
      <Pagination 
        :currentPage="currentPage" 
        :totalPages="totalPages" 
        :totalItems="applications.length" 
        :itemsPerPage="itemsPerPage" 
        @update:currentPage="currentPage = $event" 
        @refresh="loadApplications"
      />
    </div>

    <!-- SPECIFICATIONS AUDIT MODAL -->
    <div v-if="selectedApp" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="max-w-lg w-full max-h-[90vh] overflow-y-auto bg-white dark:bg-[#161B22] rounded-3xl p-6 shadow-2xl space-y-5 text-[#1E2328] dark:text-[#F0F6FC] border border-gray-100 dark:border-[#30363D] animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#30363D] pb-3">
          <div class="flex items-center gap-2.5">
            <div :class="[
              'p-2.5 rounded-xl text-white font-bold',
              selectedApp.capability_type === 'farmer' ? 'bg-[#1E9444]' : 'bg-[#0B57D0]'
            ]">
              <Sprout v-if="selectedApp.capability_type === 'farmer'" class="w-5 h-5" />
              <Building2 v-else class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-[#1E2328] dark:text-[#F0F6FC]">
                {{ selectedApp.capability_type === 'farmer' ? $t('admin.auditFarmerModal') : $t('admin.auditBuyerModal') }}
              </h3>
              <p class="text-[11px] text-[#5A6270] dark:text-[#8B949E]">{{ $t('admin.applicant') }} <strong>{{ getApplicantName(selectedApp) }}</strong> ({{ selectedApp.user?.phone || $t('common.notProvided') }})</p>
            </div>
          </div>
          <button @click="selectedApp = null" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Specifications Breakdown -->
        <div class="space-y-3 text-xs bg-gray-50 dark:bg-[#21262D] p-4 rounded-2xl border border-gray-200 dark:border-[#30363D]">
          <h4 class="font-black text-[#1E2328] dark:text-[#F0F6FC] uppercase text-[11px] tracking-wider border-b pb-2 border-gray-200 dark:border-[#30363D] flex items-center justify-between">
            <span>{{ $t('admin.submittedCapabilitySpecs') }}</span>
            <span class="text-[#0B57D0] dark:text-blue-400 capitalize">{{ selectedApp.capability_type === 'farmer' ? $t('farmer.roleFarmer') : $t('buyer.roleBuyer') }}</span>
          </h4>

          <template v-if="selectedApp.capability_type === 'farmer'">
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.farmSizeHectares') }}</span>
              <span class="font-black text-[#1E2328] dark:text-[#F0F6FC] text-sm">{{ getAppValue(selectedApp, 'farm_size', 'farmSize') ? getAppValue(selectedApp, 'farm_size', 'farmSize') + ' ' + $t('farmer.newListing.hectares') : '10 ' + $t('farmer.newListing.hectares') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.primaryCropsProduced') }}</span>
              <span class="font-black text-[#1E9444] dark:text-emerald-400">{{ getAppValue(selectedApp, 'primary_crops', 'primaryCrops') || 'Coffee, Teff, Sesame' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.farmingRegionLocation') }}</span>
              <span class="font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ getAppValue(selectedApp, 'region') || selectedApp.user?.region || 'Addis Ababa' }}</span>
            </div>
            <div class="flex justify-between py-2">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.coopUnionFarmName') }}</span>
              <span class="font-bold text-[#0B57D0] dark:text-blue-400">{{ getAppValue(selectedApp, 'union_name', 'unionName') || $t('admin.independentProducer') }}</span>
            </div>
          </template>

          <template v-else>
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.companyBusinessName') }}</span>
              <span class="font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ getAppValue(selectedApp, 'company_name', 'companyName') || $t('common.notSpecified') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.businessType') }}</span>
              <span class="font-bold text-[#0B57D0] dark:text-blue-400 capitalize">{{ getAppValue(selectedApp, 'business_type', 'businessType') || 'Wholesaler' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200/60 dark:border-[#30363D]">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.tinNumber') }}</span>
              <span class="font-mono font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ getAppValue(selectedApp, 'tin_number', 'tinNumber') || $t('common.notSpecified') }}</span>
            </div>
            <div class="flex justify-between py-2">
              <span class="text-gray-600 dark:text-[#8B949E] font-bold">{{ $t('admin.operatingRegionCity') }}</span>
              <span class="font-bold text-[#1E2328] dark:text-[#F0F6FC]">{{ getAppValue(selectedApp, 'region') || 'Addis Ababa' }}</span>
            </div>
          </template>
        </div>

        <!-- Attached Verification Documents Section -->
        <div class="space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-extrabold text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.attachedLegalDocs') }}</span>
            <span v-if="getDocsList(selectedApp).length > 0" class="text-[10px] text-gray-500 dark:text-[#8B949E] font-bold">{{ $t('admin.clickFileToView') }}</span>
          </div>
          <div v-if="getDocsList(selectedApp).length > 0" class="space-y-2">
            <div 
              v-for="(doc, i) in getDocsList(selectedApp)" 
              :key="i" 
              @click="openDoc(doc, selectedApp)"
              class="p-3 bg-emerald-50 dark:bg-emerald-950/30 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/40 border border-[#C3EFCF] dark:border-emerald-800/50 rounded-xl flex items-center justify-between transition-all cursor-pointer group shadow-2xs"
            >
              <div class="flex items-center gap-2 text-[#0F5C2A] dark:text-emerald-300 font-bold min-w-0">
                <FileText class="w-4.5 h-4.5 text-[#1E9444] dark:text-emerald-400 shrink-0" />
                <span class="font-mono text-xs truncate group-hover:underline">{{ getDocName(doc) }}</span>
              </div>
              <span class="px-2.5 py-1 bg-[#1E9444] group-hover:bg-[#0F5C2A] text-white rounded-lg text-[10px] font-black flex items-center gap-1 shadow-2xs transition-colors shrink-0">
                <ExternalLink class="w-3 h-3" />
                <span>{{ $t('admin.openAttachedFile') }}</span>
              </span>
            </div>
          </div>
          <div v-else class="p-3 bg-gray-100 dark:bg-[#21262D] rounded-xl text-gray-500 dark:text-[#8B949E] font-bold italic text-center">
            {{ $t('admin.noDocAttached') }}
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex gap-2.5 pt-3 border-t border-gray-100 dark:border-[#30363D]">
          <button @click="openRejectModal(selectedApp); selectedApp = null" 
            class="flex-1 py-2.5 border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-xl font-bold text-xs transition-colors cursor-pointer">
            {{ $t('admin.rejectApplication') }}
          </button>
          <button @click="handleApprove(selectedApp.id); selectedApp = null" 
            class="flex-1 py-2.5 bg-[#1E9444] hover:bg-[#0F5C2A] text-white rounded-xl font-bold text-xs transition-colors shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
            <Check class="w-4 h-4" />
            <span>{{ $t('admin.approveCapability') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- REJECTION REASON POPUP MODAL CARD -->
    <div v-if="rejectingApp" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="max-w-md w-full max-h-[90vh] overflow-y-auto bg-white dark:bg-[#161B22] rounded-3xl p-6 shadow-2xl space-y-4 text-[#1E2328] dark:text-[#F0F6FC] border border-red-100 dark:border-red-900/40 animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#30363D] pb-3">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-xl">
              <AlertCircle class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('admin.rejectCapabilityApplication') }}</h3>
              <p class="text-[11px] text-[#5A6270] dark:text-[#8B949E]">{{ $t('admin.applicant') }} {{ getApplicantName(rejectingApp) }}</p>
            </div>
          </div>
          <button @click="rejectingApp = null" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <label class="block font-extrabold text-[#1E2328] dark:text-[#F0F6FC]">
            {{ $t('admin.reasonForRejection') }} <span class="text-red-600">*</span>
          </label>
          <p class="text-[11px] text-gray-500 dark:text-[#8B949E]">
            {{ $t('admin.rejectionReasonExplanation') }}
          </p>

          <textarea 
            v-model="rejectionReasonInput" 
            rows="3" 
            :placeholder="$t('admin.rejectionPlaceholder')" 
            class="w-full p-3 bg-gray-50 dark:bg-[#21262D] border border-gray-200 dark:border-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] placeholder:text-gray-400 dark:placeholder:text-[#8B949E] rounded-xl font-medium text-xs focus:outline-none focus:border-red-500 focus:bg-white dark:focus:bg-[#161B22] transition-all"
          />

          <!-- Quick Suggestion Badges -->
          <div class="space-y-1.5">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">{{ $t('admin.quickReasons') }}</span>
            <div class="flex flex-wrap gap-1.5">
              <button 
                type="button"
                v-for="preset in localizedPresetReasons" 
                :key="preset" 
                @click="rejectionReasonInput = preset"
                class="px-2.5 py-1 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 border border-red-200 dark:border-red-800/50 rounded-lg text-[10px] font-extrabold transition-colors cursor-pointer text-left"
              >
                {{ preset }}
              </button>
            </div>
          </div>

          <div v-if="rejectError" class="p-2.5 bg-red-100 dark:bg-red-950/50 text-red-800 dark:text-red-200 text-[11px] font-bold rounded-lg flex items-center gap-1.5">
            <AlertCircle class="w-3.5 h-3.5 text-red-600 dark:text-red-400 shrink-0" />
            <span>{{ rejectError }}</span>
          </div>
          <div v-else class="text-[11px] font-bold text-[#9BA1AA] dark:text-[#8B949E] italic">{{ $t('admin.noDocAttached') }}</div>
        </div>

        <!-- Modal Actions -->
        <div class="flex gap-2.5 pt-2 border-t border-gray-100 dark:border-[#30363D]">
          <button @click="rejectingApp = null" class="flex-1 py-2.5 border border-gray-200 dark:border-[#30363D] rounded-xl font-bold text-xs text-gray-700 dark:text-[#8B949E] hover:bg-gray-50 dark:hover:bg-[#21262D] transition-colors cursor-pointer">
            {{ $t('common.cancel') }}
          </button>
          <button @click="confirmReject" :disabled="isSubmittingReject"
            class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xs transition-colors shadow-sm flex items-center justify-center gap-1.5 disabled:opacity-50 cursor-pointer">
            <Loader2 v-if="isSubmittingReject" class="w-4 h-4 animate-spin" />
            <span>{{ $t('admin.confirmRejection') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- IN-APP DOCUMENT PREVIEW MODAL -->
    <div v-if="isDocPreviewOpen" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="max-w-3xl w-full bg-white dark:bg-[#161B22] rounded-3xl p-6 shadow-2xl space-y-4 text-[#1E2328] dark:text-[#F0F6FC] border border-gray-100 dark:border-[#30363D] animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#30363D] pb-3 shrink-0">
          <div class="flex items-center gap-3 min-w-0">
            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-[#1E9444] dark:text-emerald-400 rounded-xl border border-emerald-100 dark:border-emerald-800/50 shrink-0">
              <FileText class="w-5 h-5" />
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-black text-[#1E2328] dark:text-[#F0F6FC] truncate">
                {{ previewDocName }}
              </h3>
              <p class="text-[11px] text-[#5A6270] dark:text-[#8B949E] font-medium truncate" v-if="previewApp">
                {{ $t('admin.applicant') }}: <strong>{{ getApplicantName(previewApp) }}</strong> ({{ previewApp.user?.phone || 'N/A' }})
              </p>
            </div>
          </div>
          <button @click="isDocPreviewOpen = false" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer shrink-0">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Preview Body Container -->
        <div class="flex-1 overflow-auto bg-gray-50 dark:bg-[#0D1117] p-4 rounded-2xl border border-gray-200 dark:border-[#30363D] min-h-[350px] flex items-center justify-center relative">
          <!-- Image Document Preview -->
          <template v-if="previewDocType === 'image'">
            <img :src="previewDocUrl" :alt="previewDocName" class="max-h-[500px] w-auto object-contain rounded-xl shadow-md border border-gray-200 dark:border-[#30363D]" />
          </template>

          <!-- PDF Document Preview -->
          <template v-else-if="previewDocType === 'pdf'">
            <div class="w-full h-[500px] flex flex-col items-center justify-center">
              <iframe :src="previewDocUrl" class="w-full h-full rounded-xl border border-gray-200 dark:border-[#30363D]"></iframe>
            </div>
          </template>

          <!-- Other Files / Fallback -->
          <template v-else>
            <div class="text-center py-10 space-y-3">
              <div class="w-16 h-16 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mx-auto border border-blue-200 dark:border-blue-800">
                <FileText class="w-8 h-8" />
              </div>
              <h4 class="text-sm font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ previewDocName }}</h4>
              <p class="text-xs text-[#5A6270] dark:text-[#8B949E] max-w-sm mx-auto">
                Attached verification file ready for audit inspection. Click download below to review on your computer.
              </p>
            </div>
          </template>
        </div>

        <!-- Modal Footer Actions -->
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-[#30363D] shrink-0">
          <button @click="openExternalWindow" class="px-4 py-2.5 border border-gray-200 dark:border-[#30363D] text-gray-700 dark:text-[#8B949E] hover:bg-gray-50 dark:hover:bg-[#21262D] rounded-xl text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5">
            <ExternalLink class="w-4 h-4" />
            <span>Open in New Tab</span>
          </button>
          <div class="flex items-center gap-2">
            <button @click="isDocPreviewOpen = false" class="px-4 py-2.5 border border-gray-200 dark:border-[#30363D] text-gray-700 dark:text-[#8B949E] hover:bg-gray-50 dark:hover:bg-[#21262D] rounded-xl text-xs font-bold transition-colors cursor-pointer">
              {{ $t('common.close') || 'Close' }}
            </button>
            <button @click="downloadPreviewDoc" class="px-5 py-2.5 bg-[#1E9444] hover:bg-[#0F5C2A] text-white rounded-xl text-xs font-black transition-colors shadow-sm cursor-pointer flex items-center gap-2">
              <Download class="w-4 h-4" />
              <span>Download File</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  FileText, Check, Loader2, Phone, Calendar, CheckCircle, 
  RefreshCcw, CheckCircle2, XCircle, Eye, Building2, Sprout, X, AlertCircle,
  Paperclip, ExternalLink, Download
} from 'lucide-vue-next'
import { adminApi } from '@/services/adminService'
import { formatDate } from '@/utils/helpers'
import { useLanguage } from '@/composables/useLanguage'
import { useAlertModal } from '@/composables/useAlertModal'
import Pagination from '@/components/common/Pagination.vue'

const { t, currentLanguage } = useLanguage()
const { showAlert } = useAlertModal()

const applications = ref([])
const isLoading = ref(true)
const selectedApp = ref(null)

const currentPage = ref(1)
const itemsPerPage = 6

const totalPages = computed(() => Math.ceil(applications.value.length / itemsPerPage) || 1)

const paginatedApplications = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return applications.value.slice(start, start + itemsPerPage)
})

const rejectingApp = ref(null)
const rejectionReasonInput = ref('')
const rejectError = ref('')
const isSubmittingReject = ref(false)

const localizedPresetReasons = computed(() => {
  if (currentLanguage.value === 'am') {
    return [
      'የተያያዘው የማረጋገጫ ሰነድ አልተገኘም ወይም አይነበብም።',
      'የእርሻ መጠን እና ዋና ዋና የሰብል ዝርዝሮች ተጨማሪ ማብራሪያ ያስፈልጋቸዋል።',
      'የግብር ከፋይ መለያ (TIN) ወይም የንግድ ምዝገባ ሊረጋገጥ አልቻለም።',
      'የተደገመ ወይም ያልተሟላ የብቃት ማመልከቻ ጥያቄ።'
    ]
  }
  return [
    'Attached verification document is missing or unreadable.',
    'Farm size and primary crop details require further clarification.',
    'Tax identification (TIN) or business registration could not be verified.',
    'Duplicate or incomplete capability application request.'
  ]
})

const loadApplications = async () => {
  isLoading.value = true
  try {
    const res = await adminApi.fetchApplications()
    applications.value = res.data || res
  } catch (err) {
    applications.value = []
    showAlert({
      title: 'Fetch Error',
      message: err.message || 'Failed to fetch applications from server.',
      type: 'error'
    })
  } finally {
    isLoading.value = false
  }
}

onMounted(loadApplications)

const getApplicantName = (app) => {
  const first = app.user?.first_name || ''
  const second = app.user?.second_name || ''
  const name = app.user?.name || ''
  return `${first} ${second}`.trim() || name || 'Applicant User'
}

const getAppValue = (app, key1, key2) => {
  const docs = app.supporting_documents
  if (docs && typeof docs === 'object' && !Array.isArray(docs)) {
    if (docs[key1] !== undefined) return docs[key1]
    if (key2 && docs[key2] !== undefined) return docs[key2]
    if (docs.application_data && typeof docs.application_data === 'object') {
      if (docs.application_data[key1] !== undefined) return docs.application_data[key1]
      if (key2 && docs.application_data[key2] !== undefined) return docs.application_data[key2]
    }
  }
  return null
}

const getDocName = (doc) => {
  if (!doc) return 'Attached Document'
  if (typeof doc === 'object') {
    if (doc.name) return doc.name
    if (doc.url) return getDocName(doc.url)
  }
  if (typeof doc === 'string') {
    if (doc.startsWith('data:')) {
      const mime = doc.split(';')[0].split(':')[1] || ''
      const ext = mime.split('/')[1] || 'file'
      return `attached_document.${ext}`
    }
    const clean = doc.split('?')[0]
    const parts = clean.split('/')
    return parts[parts.length - 1] || 'Attached Document'
  }
  return 'Attached Document'
}

const getDocUrl = (doc) => {
  if (!doc) return '#'
  let target = doc
  if (typeof doc === 'object') {
    target = doc.url || doc.path || doc.name || '#'
  }
  if (typeof target !== 'string') return '#'

  if (target.startsWith('data:') || target.startsWith('blob:') || target.startsWith('http://') || target.startsWith('https://')) {
    return target
  }

  if (target.startsWith('/')) {
    return `http://127.0.0.1:8000${target}`
  }
  if (target.startsWith('documents/') || target.startsWith('uploads/') || target.startsWith('storage/')) {
    return `http://127.0.0.1:8000/storage/${target.replace(/^storage\//, '')}`
  }

  return `http://127.0.0.1:8000/storage/${target}`
}

const isDocPreviewOpen = ref(false)
const previewDocUrl = ref('')
const previewDocName = ref('')
const previewDocType = ref('pdf')
const previewApp = ref(null)

const openDoc = async (doc, app = null) => {
  const url = getDocUrl(doc)
  const docName = getDocName(doc)

  if (!url || url === '#') {
    showAlert({
      title: 'Document Unavailable',
      message: 'Document file URL is not available.',
      type: 'warning'
    })
    return
  }

  previewDocName.value = docName
  previewApp.value = app

  // Detect file type
  const lowerUrl = url.toLowerCase()
  const lowerName = docName.toLowerCase()

  if (lowerUrl.startsWith('data:image/') || lowerName.endsWith('.png') || lowerName.endsWith('.jpg') || lowerName.endsWith('.jpeg') || lowerName.endsWith('.webp') || lowerName.endsWith('.svg')) {
    previewDocType.value = 'image'
  } else if (lowerUrl.startsWith('data:application/pdf') || lowerName.endsWith('.pdf') || lowerUrl.includes('.pdf')) {
    previewDocType.value = 'pdf'
  } else {
    previewDocType.value = 'other'
  }

  // Handle Base64 Data URLs and HTTP URLs cleanly as Blob URLs
  if (url.startsWith('data:')) {
    try {
      const arr = url.split(',')
      const mimeMatch = arr[0].match(/:(.*?);/)
      const mime = mimeMatch ? mimeMatch[1] : 'application/octet-stream'
      const bstr = atob(arr[1])
      let n = bstr.length
      const u8arr = new Uint8Array(n)
      while (n--) {
        u8arr[n] = bstr.charCodeAt(n)
      }
      const blob = new Blob([u8arr], { type: mime })
      previewDocUrl.value = URL.createObjectURL(blob)
    } catch (e) {
      console.error('Failed to parse base64 document blob:', e)
      previewDocUrl.value = url
    }
  } else if (url.startsWith('http://') || url.startsWith('https://')) {
    try {
      const resp = await fetch(url)
      if (!resp.ok) throw new Error(`HTTP error ${resp.status}`)
      const rawBlob = await resp.blob()
      const pdfBlob = new Blob([rawBlob], { type: previewDocType.value === 'pdf' ? 'application/pdf' : rawBlob.type })
      previewDocUrl.value = URL.createObjectURL(pdfBlob)
    } catch (err) {
      console.warn('Direct blob fetch failed, falling back to direct URL:', err)
      previewDocUrl.value = url
    }
  } else {
    previewDocUrl.value = url
  }

  isDocPreviewOpen.value = true
}

const downloadPreviewDoc = () => {
  if (!previewDocUrl.value) return
  const a = document.createElement('a')
  a.href = previewDocUrl.value
  a.download = previewDocName.value || 'document'
  a.target = '_blank'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

const openExternalWindow = () => {
  if (!previewDocUrl.value) return
  window.open(previewDocUrl.value, '_blank')
}

const getDocsList = (app) => {
  if (!app || !app.supporting_documents) return []
  const docs = app.supporting_documents

  const parseDocItem = (item) => {
    if (!item) return null
    if (typeof item === 'string' && item.trim().length > 0) {
      return item.trim()
    }
    if (typeof item === 'object' && (item.name || item.url || item.path)) {
      return item
    }
    return null
  }

  let list = []

  if (Array.isArray(docs)) {
    list = docs.map(parseDocItem).filter(Boolean)
  } else if (typeof docs === 'object') {
    if (Array.isArray(docs.files)) {
      list = docs.files.map(parseDocItem).filter(Boolean)
    } else if (Array.isArray(docs.documents)) {
      list = docs.documents.map(parseDocItem).filter(Boolean)
    } else {
      Object.keys(docs).forEach(k => {
        if (k === 'application_data') return
        const val = docs[k]
        if (Array.isArray(val)) {
          val.forEach(v => {
            const parsed = parseDocItem(v)
            if (parsed) list.push(parsed)
          })
        } else {
          const parsed = parseDocItem(val)
          if (parsed) {
            if (typeof parsed === 'string') {
              if (!['farmer', 'buyer', 'pending', 'approved', 'rejected'].includes(parsed)) {
                list.push(parsed)
              }
            } else {
              list.push(parsed)
            }
          }
        }
      })
    }
  }

  return list
}

const openDetailModal = (app) => {
  selectedApp.value = app
}

const openRejectModal = (app) => {
  rejectingApp.value = app
  rejectionReasonInput.value = ''
  rejectError.value = ''
}

const confirmReject = async () => {
  if (!rejectionReasonInput.value.trim()) {
    rejectError.value = t('admin.specifyRejectionReason')
    return
  }
  
  isSubmittingReject.value = true
  try {
    await adminApi.rejectApplication(rejectingApp.value.id, rejectionReasonInput.value.trim())
    rejectingApp.value = null
    loadApplications()
  } catch (err) {
    rejectError.value = err.message || 'Failed to reject application'
  } finally {
    isSubmittingReject.value = false
  }
}

const handleApprove = async (id) => {
  try {
    await adminApi.approveApplication(id)
    loadApplications()
  } catch (err) {
    showAlert({
      title: 'Approval Error',
      message: err.message || 'Failed to approve application',
      type: 'error'
    })
  }
}
</script>
