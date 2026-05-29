<script setup>
import Sidebar from '../components/Sidebar.vue'
import { useRouter } from 'vue-router'
import { LogOut, User } from 'lucide-vue-next'
import api from '../axios'

const router = useRouter()

const handleLogout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout error', error)
  } finally {
    localStorage.removeItem('token')
    router.push('/login')
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Navbar Minimalist -->
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
        <h1 class="text-xl font-semibold text-slate-800 tracking-tight">{{ $route.name }}</h1>
        
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 text-slate-600 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
            <User class="w-4 h-4" />
            <span class="text-sm font-medium">Admin SPW</span>
          </div>
          <button @click="handleLogout" class="flex items-center gap-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium">
            <LogOut class="w-4 h-4" />
            Logout
          </button>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-auto">
        <div class="max-w-6xl mx-auto">
          <router-view></router-view>
        </div>
      </main>
    </div>
  </div>
</template>
