<template>
  <ResponsiveLayout :navItems="navItems" theme="light" />
</template>

<script setup>
import { computed } from 'vue'
import { Home, Store, ShoppingCart, Package } from 'lucide-vue-next'
import ResponsiveLayout from './ResponsiveLayout.vue'
import { useOrders } from '@/composables/useOrders'
import { useCart } from '@/composables/useCart'

const { orders } = useOrders()
const { totalCartCount } = useCart()

const activeOrdersCount = computed(() => {
  return orders.value.filter(o => ['placed', 'confirmed', 'dispatched', 'in_transit'].includes(o.status)).length
})

const navItems = computed(() => [
  { path: '/buyer', label: 'Dashboard', icon: Home, category: 'OVERVIEW' },
  { path: '/buyer/marketplace', label: 'Marketplace', icon: Store, category: 'PROCUREMENT & MARKETPLACE' },
  { path: '/buyer/cart', label: 'Carts', icon: ShoppingCart, category: 'PROCUREMENT & MARKETPLACE', badge: totalCartCount.value > 0 ? totalCartCount.value : null },
  { path: '/buyer/orders', label: 'Orders', icon: Package, category: 'PROCUREMENT & MARKETPLACE', badge: activeOrdersCount.value > 0 ? activeOrdersCount.value : null },
])
</script>
