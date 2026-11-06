<template>
  <ResponsiveLayout :navItems="navItems" theme="light" />
</template>

<script setup>
import { Home, FileText, Package, ShoppingCart, Shield, Users } from 'lucide-vue-next'
import ResponsiveLayout from './ResponsiveLayout.vue'

const navItems = [
  { path: '/admin', label: 'Dashboard', icon: Home, category: 'OVERVIEW' },
  { path: '/admin/applications', label: 'Applications', icon: FileText, category: 'VERIFICATION & GOVERNANCE' },
  { path: '/admin/listings', label: 'Listings', icon: Package, category: 'VERIFICATION & GOVERNANCE' },
  { path: '/admin/users', label: 'Users', icon: Users, category: 'VERIFICATION & GOVERNANCE' },
  { path: '/admin/orders', label: 'Orders', icon: ShoppingCart, category: 'TRANSACTIONS & ESCROW' },
  { path: '/admin/disputes', label: 'Escrow Disputes', icon: Shield, category: 'TRANSACTIONS & ESCROW' },
]
</script>
