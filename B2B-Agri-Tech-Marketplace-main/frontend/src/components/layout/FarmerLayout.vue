<template>
  <ResponsiveLayout :navItems="navItems" theme="farmerDark" />
</template>

<script setup>
import { Home, Package, ShoppingCart, Wallet } from 'lucide-vue-next'
import ResponsiveLayout from './ResponsiveLayout.vue'

const navItems = [
  { path: '/farmer', label: 'Dashboard', icon: Home },
  { path: '/farmer/listings', label: 'My Listings', icon: Package },
  { path: '/farmer/orders', label: 'Orders', icon: ShoppingCart },
  { path: '/farmer/payouts', label: 'Payouts Hub', icon: Wallet },
]
</script>
