<template>
  <div class="min-h-screen bg-[#EEF2F6] dark:bg-[#0D1117] flex items-center justify-center p-4">
    <div class="w-full max-w-[520px] bg-white dark:bg-[#161B22] border border-[#E2E8F0] dark:border-[#30363D] rounded-[24px] shadow-xl overflow-hidden relative">
      <!-- Top Color Accent Bar -->
      <div 
        :class="[
          'h-[5px] w-full bg-gradient-to-r',
          hasRejected ? 'from-red-500 via-rose-600 to-amber-500' : 'from-[#0B57D0] via-[#E69500] to-[#1E9444]'
        ]"
      />

      <div class="p-6 md:p-8 space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#30363D] pb-4">
          <div class="flex items-center gap-3">
            <button @click="$router.back()" class="w-9 h-9 rounded-full bg-gray-100 dark:bg-[#21262D] flex items-center justify-center hover:bg-gray-200 dark:hover:bg-[#30363D] text-[#1E2328] dark:text-[#F0F6FC] transition-colors cursor-pointer">
              <ArrowLeft class="w-5 h-5" />
            </button>
            <div>
              <h1 class="text-[18px] font-black text-[#1E2328] dark:text-[#F0F6FC] tracking-tight">
                {{ hasRejected ? $t('Application Status') : (isComplete || hasPending ? $t('Capability Application Status') : $t('auth.capabilityApplicationTitle')) }}
              </h1>
              <p class="text-[12px] text-[#5A6270] dark:text-[#8B949E]">{{ $t('auth.capabilityApplicationSub') }}</p>
            </div>
          </div>
        </div>

        <!-- Alert Banner for Errors -->
        <div v-if="submitError" class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 rounded-2xl p-4 flex items-start gap-3 text-xs text-red-700 dark:text-red-300">
          <AlertCircle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
          <div>
            <span class="font-black block">{{ $t('auth.regAlert') }}</span>
            <span>{{ $t(submitError) }}</span>
          </div>
        </div>

        <!-- REJECTED SCREEN (If Admin Rejected the Application) -->
        <div v-if="hasRejected && !isReapplying" class="text-center py-4 space-y-5">
          <div class="w-20 h-20 rounded-full bg-red-50 dark:bg-red-950/40 border-2 border-red-500 text-red-600 dark:text-red-400 flex items-center justify-center shadow-md mx-auto">
            <XCircle class="w-10 h-10 stroke-[2.5]" />
          </div>

          <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
              <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse" />
              <span>{{ $t('Application Rejected by Admin') }}</span>
            </span>

            <h3 class="text-[19px] font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Action Required for Capability Upgrade') }}</h3>
            <p class="text-[13px] text-[#5A6270] dark:text-[#8B949E] max-w-md mx-auto leading-relaxed">
              {{ $t('Your application was reviewed and rejected by the administration team.') }}
            </p>
          </div>

          <!-- REJECTION REASON CARD -->
          <div class="p-4 bg-red-50/80 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-2xl text-left space-y-2 text-xs shadow-2xs">
            <div class="flex items-center gap-2 text-red-900 dark:text-red-200 font-black">
              <AlertCircle class="w-4.5 h-4.5 text-red-600 shrink-0" />
              <span>{{ $t('Admin Rejection Reason:') }}</span>
            </div>
            <p class="text-red-950 dark:text-red-200 font-bold text-[13px] bg-white dark:bg-[#161B22] p-3 rounded-xl border border-red-200 dark:border-red-900/50 leading-relaxed italic">
              "{{ rejectionReason }}"
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
            <button 
              @click="startReapplication" 
              class="flex-1 py-3.5 rounded-2xl bg-[#1E9444] hover:bg-[#0F5C2A] text-white font-extrabold text-[14px] shadow-md transition-colors cursor-pointer flex items-center justify-center gap-2"
            >
              <RotateCcw class="w-4 h-4" />
              <span>{{ $t('Re-Apply with Corrected Info') }}</span>
            </button>
            <button 
              @click="goToDashboard" 
              class="py-3.5 px-5 rounded-2xl border border-gray-200 dark:border-[#30363D] text-gray-700 dark:text-[#8B949E] font-extrabold text-[14px] hover:bg-gray-50 dark:hover:bg-[#21262D] transition-colors cursor-pointer"
            >
              {{ $t('Dashboard') }}
            </button>
          </div>
        </div>

        <!-- APPLICATION FORM (If no pending application & not complete) -->
        <form v-else-if="!isComplete && !hasPending" @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Step 1: Select Capability Type -->
          <div>
            <label class="text-[12px] font-bold text-[#1E2328] dark:text-[#F0F6FC] block mb-2">{{ $t('Select Capability to Apply For') }}</label>
            <div class="grid grid-cols-2 gap-3 text-xs">
              <!-- Farmer Button -->
              <div 
                @click="capRole = 'farmer'" 
                :class="[
                  'p-3.5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col items-center gap-2 text-center',
                  capRole === 'farmer' ? 'border-[#1E9444] bg-[#EDFAF2] dark:bg-emerald-950/30 text-[#0F5C2A] dark:text-emerald-300 font-black shadow-2xs' : 'border-[#E2E8F0] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[#5A6270] dark:text-[#8B949E] hover:border-gray-300 dark:hover:border-gray-600 font-bold'
                ]"
              >
                <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', capRole === 'farmer' ? 'bg-[#1E9444] text-white' : 'bg-gray-100 dark:bg-[#21262D] text-gray-600 dark:text-gray-300']">
                  <Tractor class="w-5 h-5" />
                </div>
                <span>{{ $t('auth.applyFarmer') }}</span>
              </div>

              <!-- Buyer Button -->
              <div 
                @click="capRole = 'buyer'" 
                :class="[
                  'p-3.5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col items-center gap-2 text-center',
                  capRole === 'buyer' ? 'border-[#0B57D0] bg-[#EEF2F6] dark:bg-blue-950/30 text-[#0B57D0] dark:text-blue-300 font-black shadow-2xs' : 'border-[#E2E8F0] dark:border-[#30363D] bg-white dark:bg-[#161B22] text-[#5A6270] dark:text-[#8B949E] hover:border-gray-300 dark:hover:border-gray-600 font-bold'
                ]"
              >
                <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', capRole === 'buyer' ? 'bg-[#0B57D0] text-white' : 'bg-gray-100 dark:bg-[#21262D] text-gray-600 dark:text-gray-300']">
                  <ShoppingBag class="w-5 h-5" />
                </div>
                <span>{{ $t('auth.applyBuyer') }}</span>
              </div>
            </div>
          </div>

          <!-- Step 2: Capability Form Fields -->
          <div class="pt-2 border-t border-gray-100 dark:border-[#30363D] space-y-4">
            <!-- FARMER FORM FIELDS -->
            <template v-if="capRole === 'farmer'">
              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.farmSizeHectares') }}</label>
                <input 
                  type="number" 
                  required 
                  min="0.5" 
                  step="0.5"
                  v-model.number="farmSize" 
                  placeholder="e.g. 12.5" 
                  class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#1E9444] transition-all" 
                />
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.primaryCrops') }}</label>
                <input 
                  type="text" 
                  required 
                  v-model="primaryCrops" 
                  placeholder="e.g. Sidama Coffee, White Teff, Sesame" 
                  class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#1E9444] transition-all" 
                />
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.selectRegion') }}</label>
                <select v-model="region" class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#1E9444] transition-all">
                  <option value="SNNPR">{{ $t('SNNPR') }}</option>
                  <option value="Oromia">{{ $t('Oromia') }}</option>
                  <option value="Amhara">{{ $t('Amhara') }}</option>
                  <option value="Tigray">{{ $t('Tigray') }}</option>
                  <option value="Somali">{{ $t('Somali') }}</option>
                  <option value="Afar">{{ $t('Afar') }}</option>
                  <option value="Benishangul">{{ $t('Benishangul') }}</option>
                  <option value="Gambela">{{ $t('Gambela') }}</option>
                  <option value="Addis Ababa">{{ $t('Addis Ababa') }}</option>
                </select>
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('farmer.primaryUnionCoops') }}</label>
                <input 
                  type="text" 
                  v-model="unionName" 
                  placeholder="e.g. Sidama Coffee Farmers Cooperative Union" 
                  class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#1E9444] transition-all" 
                />
              </div>

              <!-- EXTRA DOCUMENT UPLOAD FIELD FOR FARMER -->
              <div class="pt-2 border-t border-gray-100 dark:border-[#30363D]">
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1 flex items-center justify-between">
                  <span>{{ $t('auth.supportingDocuments') }}</span>
                  <span class="text-[11px] font-bold text-[#1E9444] dark:text-emerald-400">PDF, Word, Excel</span>
                </label>
                
                <input 
                  type="file" 
                  ref="fileInput" 
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" 
                  class="hidden" 
                  @change="handleFileSelect" 
                />

                <div v-if="!attachedDocName" 
                  @click="triggerFileSelect"
                  class="border-2 border-dashed border-gray-200 dark:border-[#30363D] hover:border-[#1E9444] dark:hover:border-emerald-500 rounded-2xl p-4 text-center cursor-pointer transition-colors bg-[#F8F9FA] dark:bg-[#21262D]/50 hover:bg-[#EDFAF2] dark:hover:bg-emerald-950/20"
                >
                  <Upload class="w-6 h-6 text-[#1E9444] dark:text-emerald-400 mx-auto mb-1.5" />
                  <span class="text-[12px] font-extrabold text-[#1E9444] dark:text-emerald-400 block">{{ $t('Click to attach Land Certificate, Kebele ID, or Registry file') }}</span>
                  <span class="text-[10px] text-gray-500 dark:text-[#8B949E] font-medium">PDF, Word (.doc/.docx), Excel (.xls/.xlsx) — Max 15MB</span>
                </div>

                <div v-else class="p-3 bg-[#EDFAF2] dark:bg-emerald-950/40 border border-[#C3EFCF] dark:border-emerald-800 rounded-2xl flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5 text-[#0F5C2A] dark:text-emerald-300 font-bold truncate">
                    <FileText class="w-4 h-4 text-[#1E9444] dark:text-emerald-400 shrink-0" />
                    <span class="truncate">{{ attachedDocName }}</span>
                  </div>
                  <button type="button" @click="removeAttachedDoc" class="p-1 text-red-500 hover:text-red-700 font-bold text-sm">
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </template>

            <!-- BUYER FORM FIELDS -->
            <template v-else>
              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.companyName') }}</label>
                <input 
                  type="text" 
                  required 
                  v-model="companyName" 
                  placeholder="e.g. Addis Grain Processing Co." 
                  class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#0B57D0] transition-all" 
                />
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.businessType') }}</label>
                <select v-model="businessType" class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#0B57D0] transition-all">
                  <option value="exporter">{{ $t('Agricultural Exporter') }}</option>
                  <option value="processor">{{ $t('Food Processor / Mill') }}</option>
                  <option value="wholesaler">{{ $t('Bulk Wholesaler') }}</option>
                  <option value="supermarket">{{ $t('Supermarket Chain') }}</option>
                  <option value="hotel">{{ $t('Hotel / Restaurant Group') }}</option>
                  <option value="distributor">{{ $t('Regional Distributor') }}</option>
                </select>
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('buyer.tinNumber') }}</label>
                <input 
                  type="text" 
                  v-model="tinNumber" 
                  placeholder="e.g. 0098765432" 
                  class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-mono font-bold text-[#1E2328] dark:text-[#F0F6FC] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#0B57D0] transition-all" 
                />
              </div>

              <div>
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1">{{ $t('auth.selectRegion') }}</label>
                <select v-model="region" class="w-full px-3.5 py-2.5 bg-[#F0F3F7] dark:bg-[#21262D] border border-transparent dark:border-[#30363D] rounded-xl text-[14px] font-bold text-[#1E2328] dark:text-[#F0F6FC] focus:outline-none focus:bg-white dark:focus:bg-[#161B22] focus:border-[#0B57D0] transition-all">
                  <option value="Addis Ababa">{{ $t('Addis Ababa') }}</option>
                  <option value="Oromia">{{ $t('Oromia') }}</option>
                  <option value="Amhara">{{ $t('Amhara') }}</option>
                  <option value="SNNPR">{{ $t('SNNPR') }}</option>
                  <option value="Dire Dawa">{{ $t('Dire Dawa') }}</option>
                  <option value="Tigray">{{ $t('Tigray') }}</option>
                </select>
              </div>

              <!-- EXTRA DOCUMENT UPLOAD FIELD FOR BUYER -->
              <div class="pt-2 border-t border-gray-100 dark:border-[#30363D]">
                <label class="text-[12px] font-extrabold text-[#1E2328] dark:text-[#F0F6FC] block mb-1 flex items-center justify-between">
                  <span>{{ $t('auth.supportingDocuments') }}</span>
                  <span class="text-[11px] font-bold text-[#0B57D0] dark:text-blue-400">PDF, Word, Excel</span>
                </label>
                
                <input 
                  type="file" 
                  ref="fileInput" 
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" 
                  class="hidden" 
                  @change="handleFileSelect" 
                />

                <div v-if="!attachedDocName" 
                  @click="triggerFileSelect"
                  class="border-2 border-dashed border-gray-200 dark:border-[#30363D] hover:border-[#0B57D0] dark:hover:border-blue-500 rounded-2xl p-4 text-center cursor-pointer transition-colors bg-[#F8F9FA] dark:bg-[#21262D]/50 hover:bg-[#EEF2F6] dark:hover:bg-blue-950/20"
                >
                  <Upload class="w-6 h-6 text-[#0B57D0] dark:text-blue-400 mx-auto mb-1.5" />
                  <span class="text-[12px] font-extrabold text-[#0B57D0] dark:text-blue-400 block">{{ $t('Click to attach Trade License or Registration file') }}</span>
                  <span class="text-[10px] text-gray-500 dark:text-[#8B949E] font-medium">PDF, Word (.doc/.docx), Excel (.xls/.xlsx) — Max 15MB</span>
                </div>

                <div v-else class="p-3 bg-[#EEF2F6] dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-2xl flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5 text-[#0B57D0] dark:text-blue-300 font-bold truncate">
                    <FileText class="w-4 h-4 text-[#0B57D0] dark:text-blue-400 shrink-0" />
                    <span class="truncate">{{ attachedDocName }}</span>
                  </div>
                  <button type="button" @click="removeAttachedDoc" class="p-1 text-red-500 hover:text-red-700 font-bold text-sm">
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </template>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isSubmitting" 
            :class="[
              'w-full py-3.5 rounded-2xl text-white font-extrabold text-[15px] shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50',
              capRole === 'farmer' ? 'bg-[#1E9444] hover:bg-[#0F5C2A]' : 'bg-[#0B57D0] hover:bg-[#0842A0]'
            ]"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
            <span>{{ $t('auth.submitApplication') }}</span>
          </button>
        </form>

        <!-- APPROVED CAPABILITY SCREEN -->
        <div v-else-if="hasApproved" class="text-center py-4 space-y-5 animate-in fade-in duration-300">
          <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-950/40 border-2 border-[#1E9444] text-[#1E9444] flex items-center justify-center shadow-lg">
            <CheckCircle2 class="w-10 h-10 stroke-[2.5]" />
          </div>

          <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-emerald-100 dark:bg-emerald-950/60 text-[#0F5C2A] dark:text-emerald-300 border border-[#C3EFCF] dark:border-emerald-800">
              <CheckCircle2 class="w-3.5 h-3.5 text-[#1E9444]" />
              <span>{{ $t('Capability Verified & Approved') }}</span>
            </span>

            <h3 class="text-[22px] font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Access Granted!') }}</h3>
            <p class="text-[13px] text-[#5A6270] dark:text-[#8B949E] max-w-md mx-auto leading-relaxed">
              {{ $t('Your capability application has been approved by administration.') }}
            </p>
          </div>

          <button 
            @click="router.push(capRole === 'farmer' ? '/farmer' : '/buyer')" 
            class="w-full py-4 rounded-2xl bg-[#1E9444] hover:bg-[#0F5C2A] text-white font-black text-[15px] shadow-lg transition-colors cursor-pointer flex items-center justify-center gap-2"
          >
            <span>{{ $t('Access Dashboard') }}</span>
            <ArrowRight class="w-5 h-5" />
          </button>
        </div>

        <!-- PENDING ADMIN APPROVAL SCREEN -->
        <div v-else class="text-center py-4 space-y-5">
          <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
            <div class="absolute inset-0 bg-amber-100 dark:bg-amber-950/30 rounded-full animate-ping opacity-25" />
            <div class="w-20 h-20 rounded-full bg-amber-50 dark:bg-amber-950/40 border-2 border-[#E69500] text-[#E69500] flex items-center justify-center shadow-md relative z-10">
              <Clock class="w-10 h-10 stroke-[2.5]" />
            </div>
          </div>

          <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 dark:bg-amber-950/60 text-[#E69500] dark:text-amber-400 border border-amber-200 dark:border-amber-800">
              <span class="w-2 h-2 rounded-full bg-[#E69500] animate-pulse" />
              <span>{{ $t('Application Pending Admin Approval') }}</span>
            </span>

            <h3 class="text-[19px] font-black text-[#1E2328] dark:text-[#F0F6FC]">{{ $t('Wait for Administrator Review') }}</h3>
            <p class="text-[13px] text-[#5A6270] dark:text-[#8B949E] max-w-md mx-auto leading-relaxed">
              {{ $t('Your capability application has been submitted and is currently awaiting admin verification.') }}
            </p>
          </div>

          <div class="p-4 bg-[#FFFBEB] dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-2xl text-left space-y-2 text-xs">
            <div class="flex items-center gap-2 text-amber-900 dark:text-amber-200 font-extrabold">
              <ShieldCheck class="w-4 h-4 text-[#E69500]" />
              <span>{{ $t('What happens next?') }}</span>
            </div>
            <ul class="space-y-1 text-amber-950 dark:text-amber-200 font-medium text-[11px] list-disc list-inside leading-normal">
              <li>{{ $t('Our platform administration team reviews your capability details.') }}</li>
              <li>{{ $t('Please wait for administrator approval before accessing role-specific pages.') }}</li>
              <li>{{ $t('Once approved, full access to the capability dashboard and features will automatically be granted.') }}</li>
            </ul>
          </div>

          <button 
            @click="goToDashboard" 
            class="w-full py-3.5 rounded-2xl bg-[#0B57D0] hover:bg-[#0842A0] text-white font-extrabold text-[14px] shadow-md transition-colors cursor-pointer"
          >
            {{ $t('Return to Dashboard') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeft, ShieldCheck, AlertCircle, Loader2, Tractor, 
  ShoppingBag, Clock, XCircle, RotateCcw, Upload, FileText, X, CheckCircle2, ArrowRight 
} from 'lucide-vue-next'
import { api, mapBackendUserToFrontend } from '@/services/api'
import { useAuth } from '@/composables/useAuth'
import { compressImageFile } from '@/utils/imageCompressor'

const router = useRouter()
const { user, isAuthenticated } = useAuth()

const capRole = ref(user.value?.role === 'buyer' ? 'farmer' : 'buyer')
const farmSize = ref(10)
const primaryCrops = ref('Coffee, Teff, Sesame')
const unionName = ref('')

const companyName = ref('')
const businessType = ref('wholesaler')
const tinNumber = ref('')
const region = ref('Addis Ababa')

const fileInput = ref(null)
const attachedDoc = ref(null)
const attachedDocName = ref('')

const isSubmitting = ref(false)
const isComplete = ref(false)
const hasPending = ref(false)
const hasRejected = ref(false)
const hasApproved = ref(false)
const isReapplying = ref(false)
const rejectionReason = ref('')
const submitError = ref(null)

const triggerFileSelect = () => {
  if (fileInput.value) fileInput.value.click()
}

const handleFileSelect = async (e) => {
  submitError.value = null
  const file = e.target.files?.[0]
  if (!file) return

  if (file.type && file.type.startsWith('image/')) {
    try {
      const compressed = await compressImageFile(file, 1400, 1400, 0.8)
      attachedDoc.value = compressed
      attachedDocName.value = compressed.name
    } catch {
      attachedDoc.value = file
      attachedDocName.value = file.name
    }
  } else if (file.size > 5 * 1024 * 1024) {
    submitError.value = 'Attached document is too large (exceeds 5MB). Please attach a smaller file or image.'
    if (fileInput.value) fileInput.value.value = ''
    attachedDoc.value = null
    attachedDocName.value = ''
  } else {
    attachedDoc.value = file
    attachedDocName.value = file.name
  }
}

const removeAttachedDoc = () => {
  attachedDoc.value = null
  attachedDocName.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

onMounted(async () => {
  if (isAuthenticated.value) {
    try {
      const res = await api.fetchMyCapabilityApplications()
      const apps = Array.isArray(res) ? res : (res?.applications || [])
      if (apps.length > 0) {
        const latestApp = apps[0]
        capRole.value = latestApp.capability_type || latestApp.capabilityType || 'farmer'
        
        if (latestApp.status === 'pending') {
          hasPending.value = true
          isComplete.value = true
        } else if (latestApp.status === 'rejected') {
          hasRejected.value = true
          rejectionReason.value = latestApp.rejection_reason || latestApp.rejectionReason || 'The application details provided could not be verified by administration.'
          isComplete.value = true
        } else if (latestApp.status === 'approved') {
          hasApproved.value = true
          isComplete.value = true

          try {
            const userRes = await api.fetchCurrentUser()
            if (userRes && userRes.user) {
              const mapped = mapBackendUserToFrontend(userRes.user)
              const savedUser = localStorage.getItem('agri_user_data')
              localStorage.setItem('agri_user_data', JSON.stringify(mapped))
              localStorage.setItem('agri_role', mapped.role)
            }
          } catch { /* ignore */ }
        }
      }
    } catch {
      // offline fallback
    }
  }
})

const startReapplication = () => {
  isReapplying.value = true
  hasRejected.value = false
  hasPending.value = false
  isComplete.value = false
}

const handleSubmit = async () => {
  submitError.value = null

  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }

  isSubmitting.value = true

  try {
    let docObj = attachedDocName.value
    if (attachedDoc.value) {
      try {
        const dataUrl = await new Promise((resolve, reject) => {
          const reader = new FileReader()
          reader.onload = () => resolve(reader.result)
          reader.onerror = reject
          reader.readAsDataURL(attachedDoc.value)
        })
        docObj = {
          name: attachedDocName.value,
          url: dataUrl
        }
      } catch {
        docObj = attachedDocName.value
      }
    }
    const docs = docObj ? [docObj] : []
    
    const payload = {
      capability_type: capRole.value,
      supporting_documents: docs,
      application_data: capRole.value === 'farmer' ? {
        farm_size: farmSize.value,
        primary_crops: primaryCrops.value,
        region: region.value,
        union_name: unionName.value,
      } : {
        company_name: companyName.value,
        business_type: businessType.value,
        tin_number: tinNumber.value,
        region: region.value,
      }
    }

    await api.submitCapabilityApplication(payload)
    isSubmitting.value = false
    isComplete.value = true
    hasPending.value = true
    hasRejected.value = false
    isReapplying.value = false
  } catch (err) {
    isSubmitting.value = false
    submitError.value = err.message || 'Failed to submit capability application. Please try again.'
  }
}

const goToDashboard = () => {
  if (user.value?.role === 'farmer') router.push('/farmer')
  else if (user.value?.role === 'admin') router.push('/admin')
  else router.push('/buyer')
}
</script>
