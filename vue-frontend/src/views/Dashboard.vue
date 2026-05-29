<script setup>
import { ref, onMounted } from 'vue'
import { Package, ShoppingCart, Wallet, ArrowRight, Plus, FileText, ListOrdered } from 'lucide-vue-next'
import api from '../axios'

const stats = ref({
  total_products: 0,
  total_transactions: 0,
  total_revenue: 0
})
const recentTransactions = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await api.get('/dashboard')
    if (response.data && response.data.data) {
      stats.value = response.data.data
    }
    
    // Fetch recent transactions
    const txResponse = await api.get('/transactions')
    if (txResponse.data) {
      recentTransactions.value = txResponse.data.reverse().slice(0, 5)
    }
  } catch (error) {
    console.error('Error fetching dashboard data', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})

const formatRp = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka)
}
</script>

<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
      <p class="text-slate-500 mt-1">Selamat datang kembali, Admin! Berikut adalah ringkasan hari ini.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Total Produk -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
          <Package class="w-8 h-8" />
        </div>
        <div>
          <p class="text-sm font-medium text-slate-500 mb-1">Total Produk</p>
          <p class="text-3xl font-bold text-slate-800">
            <span v-if="loading" class="animate-pulse bg-slate-200 text-transparent rounded w-16 inline-block">000</span>
            <span v-else>{{ stats.total_products }}</span>
          </p>
        </div>
      </div>

      <!-- Transaksi -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
          <ShoppingCart class="w-8 h-8" />
        </div>
        <div>
          <p class="text-sm font-medium text-slate-500 mb-1">Transaksi Hari Ini</p>
          <p class="text-3xl font-bold text-slate-800">
            <span v-if="loading" class="animate-pulse bg-slate-200 text-transparent rounded w-16 inline-block">000</span>
            <span v-else>{{ stats.total_transactions }}</span>
          </p>
        </div>
      </div>

      <!-- Pendapatan -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center shrink-0">
          <Wallet class="w-8 h-8" />
        </div>
        <div>
          <p class="text-sm font-medium text-slate-500 mb-1">Pendapatan Hari Ini</p>
          <p class="text-3xl font-bold text-slate-800">
            <span v-if="loading" class="animate-pulse bg-slate-200 text-transparent rounded w-32 inline-block">Rp 000.000</span>
            <span v-else>{{ formatRp(stats.total_revenue) }}</span>
          </p>
        </div>
      </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- Aksi Cepat -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100">
          <h3 class="font-bold text-lg text-slate-800">Aksi Cepat</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
          <router-link to="/transactions" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 transition-colors text-slate-600 group">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <Plus class="w-6 h-6" />
            </div>
            <span class="font-medium text-sm">Kasir Baru</span>
          </router-link>
          
          <router-link to="/products/create" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 transition-colors text-slate-600 group">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <Package class="w-6 h-6" />
            </div>
            <span class="font-medium text-sm">Tambah Item</span>
          </router-link>
          
          <router-link to="/reports" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-purple-50 hover:border-purple-200 hover:text-purple-700 transition-colors text-slate-600 group">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
              <FileText class="w-6 h-6" />
            </div>
            <span class="font-medium text-sm">Cek Laporan</span>
          </router-link>
        </div>
      </div>

      <!-- Transaksi Terakhir -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-lg text-slate-800">Transaksi Terakhir</h3>
          <router-link to="/reports" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</router-link>
        </div>
        <div class="flex-1 overflow-auto">
          <div v-if="loading" class="p-6 flex justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          <div v-else-if="recentTransactions.length === 0" class="p-8 text-center text-slate-500">
            Belum ada transaksi hari ini.
          </div>
          <ul v-else class="divide-y divide-slate-100">
            <li v-for="tx in recentTransactions" :key="tx.id" class="p-4 sm:px-6 flex items-center justify-between hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                  <ListOrdered class="w-5 h-5" />
                </div>
                <div>
                  <p class="font-semibold text-slate-800">{{ tx.product ? tx.product.nama_produk : 'Produk Dihapus' }}</p>
                  <p class="text-xs text-slate-500">{{ new Date(tx.created_at).toLocaleString('id-ID') }} • {{ tx.jumlah }} pcs</p>
                </div>
              </div>
              <div class="font-bold text-slate-800">
                {{ formatRp(tx.total_harga) }}
              </div>
            </li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</template>
