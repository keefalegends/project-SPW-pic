<script setup>
import { useRoute } from 'vue-router'
import { LayoutDashboard, Package, ShoppingCart, FileText, Store } from 'lucide-vue-next'

const route = useRoute()

const menu = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Produk', path: '/products', icon: Package },
  { name: 'Transaksi', path: '/transactions', icon: ShoppingCart },
  { name: 'Laporan', path: '/reports', icon: FileText },
]

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>

<template>
  <aside class="w-64 bg-white border-r border-slate-200 flex flex-col shadow-sm z-20 hidden md:flex">
    <!-- Logo/Brand -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200">
      <div class="flex items-center gap-3 text-blue-600">
        <Store class="w-7 h-7" />
        <span class="text-xl font-bold tracking-tight">SPW Application</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6 px-4 space-y-1 overflow-y-auto">
      <router-link 
        v-for="item in menu" 
        :key="item.path" 
        :to="item.path"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
          isActive(item.path) 
            ? 'bg-blue-50 text-blue-700 shadow-sm' 
            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
        ]"
      >
        <component :is="item.icon" :class="[
          'w-5 h-5 transition-colors',
          isActive(item.path) ? 'text-blue-600' : 'text-slate-400'
        ]" />
        {{ item.name }}
      </router-link>
    </nav>
  </aside>
</template>
