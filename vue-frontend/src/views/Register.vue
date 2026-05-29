<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Store, UserPlus, Loader2 } from 'lucide-vue-next'
import api from '../axios'

const router = useRouter()
const form = ref({
  name: '',
  email: '',
  password: ''
})
const errorMsg = ref('')
const loading = ref(false)

const handleRegister = async () => {
  errorMsg.value = ''
  loading.value = true
  try {
    const response = await api.post('/register', {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password
    })
    
    if (response.data && response.data.token) {
      localStorage.setItem('token', response.data.token)
      router.push('/')
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMsg.value = error.response.data.message
    } else {
      errorMsg.value = 'Terjadi kesalahan pada saat registrasi.'
    }
    console.error('Register Error', error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
      <!-- Header -->
      <div class="bg-blue-600 px-8 py-10 text-center relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500 rounded-full opacity-50 blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-700 rounded-full opacity-50 blur-2xl"></div>
        <div class="relative z-10">
          <div class="w-16 h-16 bg-white rounded-xl mx-auto flex items-center justify-center mb-4 shadow-lg shadow-blue-500/30">
            <Store class="w-8 h-8 text-blue-600" />
          </div>
          <h2 class="text-2xl font-bold text-white mb-1">SPW Application</h2>
          <p class="text-blue-100 text-sm">Buat akun admin baru</p>
        </div>
      </div>

      <!-- Form -->
      <div class="p-8">
        <form @submit.prevent="handleRegister" class="space-y-5">
          <div v-if="errorMsg" class="bg-rose-50 text-rose-600 px-4 py-3 rounded-lg text-sm font-medium border border-rose-100">
            {{ errorMsg }}
          </div>

          <div class="space-y-1">
            <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 bg-slate-50/50 focus:bg-white"
              placeholder="Admin SPW"
            >
          </div>

          <div class="space-y-1">
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              required
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 bg-slate-50/50 focus:bg-white"
              placeholder="admin@spw.com"
            >
          </div>

          <div class="space-y-1">
            <label class="text-sm font-medium text-slate-700">Password</label>
            <input 
              v-model="form.password" 
              type="password" 
              required
              minlength="6"
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 bg-slate-50/50 focus:bg-white"
              placeholder="••••••••"
            >
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg shadow-sm shadow-blue-600/20 hover:shadow-md hover:shadow-blue-600/30 transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
            <UserPlus v-else class="w-5 h-5" />
            <span>{{ loading ? 'Memproses...' : 'Daftar Sekarang' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <router-link to="/login" class="text-sm text-slate-500 hover:text-blue-600 font-medium transition-colors">Sudah punya akun? Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
