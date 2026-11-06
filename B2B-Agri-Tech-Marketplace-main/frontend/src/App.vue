<template>
  <router-view />
  <LogoutModal />
  <NoticeModal />
  <PwaInstallPrompt />
</template>

<script setup>
import LogoutModal from '@/components/common/LogoutModal.vue'
import NoticeModal from '@/components/common/NoticeModal.vue'
import PwaInstallPrompt from '@/components/common/PwaInstallPrompt.vue'
</script>
