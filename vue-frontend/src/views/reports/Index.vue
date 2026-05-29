<script setup>
import { ref, onMounted } from 'vue'
import { Calendar, TrendingUp, DollarSign, Award, Search, Loader2 } from 'lucide-vue-next'
import api from '../../axios'

const startDate = ref('')
const endDate = ref('')
const loading = ref(false)

const reportData = ref({
  transactions: [],
  summary: {
    total_revenue: 0,
    total_profit: 0
  }
})

const topProduct = ref(null)

const fetchReport = async () => {
  loading.value = true
  try {
    const params = {}
    if (startDate.value && endDate.value) {
      params.start = startDate.value
      params.end = endDate.value
    }
    
    const response = await api.get('/reports', { params })
    if (response.data && response.data.data) {
      reportData.value = response.data.data
      calculateTopProduct()
    }
  } catch (error) {
    console.error('Error fetching report', error)
  } finally {
    loading.value = false
  }
}

const calculateTopProduct = () => {
  const productCount = {}
  let maxProduct = null
  let maxQty = 0

  reportData.value.transactions.forEach(trx => {
    if (!trx.product) return
    const id = trx.product.id
    if (!productCount[id]) {
      productCount[id] = { name: trx.product.nama_produk, qty: 0 }
    }
    productCount[id].qty += trx.jumlah
    
    if (productCount[id].qty > maxQty) {
      maxQty = productCount[id].qty
      maxProduct = productCount[id].name
    }
  })

  topProduct.value = maxProduct ? `${maxProduct} (${maxQty} terjual)` : '-'
}

onMounted(() => {
  // Set default to current month
  const date = new Date()
  const firstDay = new Date(date.getFullYear(), date.getMonth(), 1)
  const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0)
  
  startDate.value = firstDay.toISOString().split('T')[0]
  endDate.value = lastDay.toISOString().split('T')[0]
  
  fetchReport()
})

const formatRp = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka)
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-800">Laporan Penjualan</h2>
      <p class="text-slate-500 mt-1">Ringkasan pendapatan dan performa penjualan aplikasi SPW.</p>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-wrap items-end gap-4">
      <div class="space-y-1">
        <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Mulai Tanggal</label>
        <div class="relative">
          <Calendar class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
          <input type="date" v-model="startDate" class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
      </div>
      <div class="space-y-1">
        <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Sampai Tanggal</label>
        <div class="relative">
          <Calendar class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
          <input type="date" v-model="endDate" class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
      </div>
      <button @click="fetchReport" class="px-5 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-900 transition-colors flex items-center gap-2">
        <Search class="w-4 h-4" />
        Terapkan Filter
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10">
          <DollarSign class="w-32 h-32 -mr-8 -mb-8" />
        </div>
        <div class="relative z-10">
          <p class="text-blue-100 text-sm font-medium mb-1">Total Pendapatan (Omzet)</p>
          <h3 class="text-3xl font-bold">{{ formatRp(reportData.summary.total_revenue) }}</h3>
        </div>
      </div>

      <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10">
          <TrendingUp class="w-32 h-32 -mr-8 -mb-8" />
        </div>
        <div class="relative z-10">
          <p class="text-emerald-100 text-sm font-medium mb-1">Total Keuntungan (Profit)</p>
          <h3 class="text-3xl font-bold">{{ formatRp(reportData.summary.total_profit) }}</h3>
        </div>
      </div>

      <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10">
          <Award class="w-32 h-32 -mr-8 -mb-8" />
        </div>
        <div class="relative z-10">
          <p class="text-amber-100 text-sm font-medium mb-1">Produk Terlaris</p>
          <h3 class="text-2xl font-bold truncate" :title="topProduct">{{ topProduct || '-' }}</h3>
        </div>
      </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <h3 class="font-bold text-slate-800">Detail Transaksi</h3>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-white text-slate-500 text-sm uppercase tracking-wider border-b border-slate-200">
              <th class="px-6 py-4 font-medium">Tanggal</th>
              <th class="px-6 py-4 font-medium">Produk</th>
              <th class="px-6 py-4 font-medium text-center">Jumlah</th>
              <th class="px-6 py-4 font-medium text-right">Modal</th>
              <th class="px-6 py-4 font-medium text-right">Harga Jual</th>
              <th class="px-6 py-4 font-medium text-right">Keuntungan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-blue-500" />
                Memuat data laporan...
              </td>
            </tr>
            <tr v-else-if="reportData.transactions.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                Tidak ada data transaksi pada rentang tanggal ini.
              </td>
            </tr>
            <tr v-for="trx in reportData.transactions" :key="trx.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-3 text-sm">{{ new Date(trx.created_at).toLocaleDateString('id-ID') }}</td>
              <td class="px-6 py-3 font-medium">{{ trx.product ? trx.product.nama_produk : 'Dihapus' }}</td>
              <td class="px-6 py-3 text-center">{{ trx.jumlah }}</td>
              <td class="px-6 py-3 text-right text-sm text-slate-500">
                {{ trx.product ? formatRp(trx.product.harga_beli * trx.jumlah) : '-' }}
              </td>
              <td class="px-6 py-3 text-right font-medium">
                {{ formatRp(trx.total_harga) }}
              </td>
              <td class="px-6 py-3 text-right font-semibold text-emerald-600">
                {{ trx.product ? formatRp((trx.product.harga_jual - trx.product.harga_beli) * trx.jumlah) : '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
