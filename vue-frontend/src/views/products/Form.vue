<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import api from '../../axios'

const router = useRouter()
const route = useRoute()
const isEdit = ref(false)
const loading = ref(false)
const errorMsg = ref('')

const form = ref({
  nama_produk: '',
  harga_beli: 0,
  harga_jual: 0,
  stok: 0
})

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true
    try {
      const response = await api.get(`/products/${route.params.id}`)
      form.value = {
        nama_produk: response.data.nama_produk,
        harga_beli: response.data.harga_beli,
        harga_jual: response.data.harga_jual,
        stok: response.data.stok
      }
    } catch (error) {
      console.error('Error fetching product', error)
      alert('Produk tidak ditemukan')
      router.push('/products')
    }
  }
})

const handleSubmit = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    if (isEdit.value) {
      await api.put(`/products/${route.params.id}`, form.value)
    } else {
      await api.post('/products', form.value)
    }
    router.push('/products')
  } catch (error) {
    console.error('Error saving product', error)
    errorMsg.value = 'Gagal menyimpan data produk. Pastikan semua field terisi dengan benar.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
      <button @click="router.push('/products')" class="p-2 bg-white rounded-full border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
        <ArrowLeft class="w-5 h-5" />
      </button>
      <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
        <p class="text-slate-500 mt-1">Lengkapi form di bawah untuk {{ isEdit ? 'mengubah' : 'menambahkan' }} data produk.</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-6">
        <div v-if="errorMsg" class="mb-6 bg-rose-50 text-rose-600 px-4 py-3 rounded-lg text-sm font-medium border border-rose-100">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Nama Produk</label>
            <input 
              v-model="form.nama_produk" 
              type="text" 
              required
              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700"
              placeholder="Contoh: Nasi Goreng Spesial"
            >
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Harga Beli (Modal)</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">Rp</span>
                </div>
                <input 
                  v-model.number="form.harga_beli" 
                  type="number" 
                  min="0"
                  required
                  class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700"
                  placeholder="0"
                >
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Harga Jual</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">Rp</span>
                </div>
                <input 
                  v-model.number="form.harga_jual" 
                  type="number" 
                  min="0"
                  required
                  class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700"
                  placeholder="0"
                >
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Stok Awal</label>
            <input 
              v-model.number="form.stok" 
              type="number" 
              min="0"
              required
              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700"
              placeholder="0"
            >
          </div>

          <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <button 
              type="button" 
              @click="router.push('/products')"
              class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="loading"
              class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              {{ loading ? 'Menyimpan...' : 'Simpan Produk' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
