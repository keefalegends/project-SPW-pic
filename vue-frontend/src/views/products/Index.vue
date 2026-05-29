<script setup>
import { ref, onMounted, computed } from 'vue'
import { Plus, Search, Edit2, Trash2 } from 'lucide-vue-next'
import api from '../../axios'

const products = ref([])
const loading = ref(true)
const searchQuery = ref('')

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get('/products')
    products.value = response.data
  } catch (error) {
    console.error('Error fetching products', error)
  } finally {
    loading.value = false
  }
}

const deleteProduct = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
    try {
      await api.delete(`/products/${id}`)
      fetchProducts()
    } catch (error) {
      console.error('Error deleting product', error)
      alert('Gagal menghapus produk')
    }
  }
}

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value
  return products.value.filter(p => p.nama_produk.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

onMounted(() => {
  fetchProducts()
})

const formatRp = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka)
}
</script>

<template>
  <div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-800">Daftar Produk</h2>
        <p class="text-slate-500 mt-1">Kelola data makanan dan minuman aplikasi SPW.</p>
      </div>
      <router-link to="/products/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm flex items-center gap-2 transition-colors">
        <Plus class="w-5 h-5" />
        Tambah Produk
      </router-link>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <!-- Search Bar -->
      <div class="p-4 border-b border-slate-200 bg-slate-50/50">
        <div class="relative max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="h-5 w-5 text-slate-400" />
          </div>
          <input 
            v-model="searchQuery" 
            type="text" 
            class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-slate-900 placeholder-slate-400" 
            placeholder="Cari nama produk..." 
          />
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-sm uppercase tracking-wider">
              <th class="px-6 py-4 font-medium border-b border-slate-200">Nama Produk</th>
              <th class="px-6 py-4 font-medium border-b border-slate-200">Harga Jual</th>
              <th class="px-6 py-4 font-medium border-b border-slate-200">Stok</th>
              <th class="px-6 py-4 font-medium border-b border-slate-200 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 text-slate-700">
            <tr v-if="loading">
              <td colspan="4" class="px-6 py-8 text-center text-slate-500">Memuat data...</td>
            </tr>
            <tr v-else-if="filteredProducts.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tidak ada produk ditemukan.</td>
            </tr>
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 font-medium text-slate-900">{{ product.nama_produk }}</td>
              <td class="px-6 py-4">{{ formatRp(product.harga_jual) }}</td>
              <td class="px-6 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', product.stok > 10 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700']">
                  {{ product.stok }} pcs
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <router-link :to="`/products/${product.id}/edit`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <Edit2 class="w-4 h-4" />
                  </router-link>
                  <button @click="deleteProduct(product.id)" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination Dummy -->
      <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-sm text-slate-500">
        <span>Menampilkan {{ filteredProducts.length }} produk</span>
        <div class="flex gap-2">
          <button disabled class="px-3 py-1 rounded border border-slate-200 bg-white text-slate-400 cursor-not-allowed">Sebelumnya</button>
          <button disabled class="px-3 py-1 rounded border border-slate-200 bg-white text-slate-400 cursor-not-allowed">Selanjutnya</button>
        </div>
      </div>
    </div>
  </div>
</template>
